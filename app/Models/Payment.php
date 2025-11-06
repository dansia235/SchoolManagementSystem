<?php
/**
 * Payment Model
 */
class Payment {
    /**
     * Get all payments with pagination
     */
    public static function all($page = 1, $per_page = null) {
        if ($per_page === null) {
            $per_page = ITEMS_PER_PAGE;
        }

        $offset = ($page - 1) * $per_page;

        $sql = '
            SELECT p.*, i.invoice_number, s.first_name, s.last_name, s.matricule, u.name as received_by_name
            FROM payments p
            JOIN invoices i ON i.id = p.invoice_id
            JOIN students s ON s.id = i.student_id
            LEFT JOIN users u ON u.id = p.received_by
            ORDER BY p.paid_at DESC
            LIMIT ? OFFSET ?
        ';

        return DB::query($sql, [$per_page, $offset]);
    }

    /**
     * Count payments
     */
    public static function count() {
        return DB::queryValue('SELECT COUNT(*) FROM payments');
    }

    /**
     * Find payment by ID
     */
    public static function find($id) {
        $sql = '
            SELECT p.*, i.invoice_number, s.first_name, s.last_name, s.matricule
            FROM payments p
            JOIN invoices i ON i.id = p.invoice_id
            JOIN students s ON s.id = i.student_id
            WHERE p.id = ?
        ';

        return DB::queryOne($sql, [$id]);
    }

    /**
     * Create payment
     */
    public static function create($data) {
        DB::beginTransaction();

        try {
            $sql = '
                INSERT INTO payments (payment_number, invoice_id, paid_at, amount, method, reference, notes, received_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ';

            DB::execute($sql, [
                $data['payment_number'] ?? generate_payment_number(),
                $data['invoice_id'],
                $data['paid_at'] ?? date('Y-m-d H:i:s'),
                $data['amount'],
                $data['method'] ?? 'CASH',
                $data['reference'] ?? null,
                $data['notes'] ?? null,
                Auth::id()
            ]);

            $payment_id = DB::lastInsertId();

            // Update invoice status
            Invoice::recalculateStatus($data['invoice_id']);

            // Add to cashbook
            $invoice = Invoice::find($data['invoice_id']);
            $student = Student::find($invoice['student_id']);

            Cashbook::addIncome(
                'Paiement Scolarité',
                'Paiement de ' . $student['first_name'] . ' ' . $student['last_name'] . ' - ' . $invoice['invoice_number'],
                $data['amount'],
                $data['method'] ?? 'CASH',
                $data['reference'] ?? null
            );

            DB::commit();

            log_activity('payment_created', 'payment', $payment_id, 'Payment recorded: ' . $data['amount']);

            return $payment_id;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Delete payment
     */
    public static function delete($id) {
        DB::beginTransaction();

        try {
            $payment = self::find($id);

            if (!$payment) {
                return false;
            }

            // Delete payment
            DB::execute('DELETE FROM payments WHERE id = ?', [$id]);

            // Recalculate invoice status
            Invoice::recalculateStatus($payment['invoice_id']);

            DB::commit();

            log_activity('payment_deleted', 'payment', $id, 'Payment deleted');

            return true;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Get payments by date range
     */
    public static function byDateRange($start_date, $end_date) {
        $sql = '
            SELECT p.*, i.invoice_number, s.first_name, s.last_name
            FROM payments p
            JOIN invoices i ON i.id = p.invoice_id
            JOIN students s ON s.id = i.student_id
            WHERE DATE(p.paid_at) BETWEEN ? AND ?
            ORDER BY p.paid_at DESC
        ';

        return DB::query($sql, [$start_date, $end_date]);
    }

    /**
     * Get payment statistics
     */
    public static function statistics($start_date = null, $end_date = null) {
        $stats = [];

        $where = '1=1';
        $params = [];

        if ($start_date && $end_date) {
            $where = 'DATE(paid_at) BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }

        // Total payments
        $stats['total_amount'] = DB::queryValue("SELECT SUM(amount) FROM payments WHERE $where", $params);

        // Count payments
        $stats['total_count'] = DB::queryValue("SELECT COUNT(*) FROM payments WHERE $where", $params);

        // By method
        $stats['by_method'] = DB::query("
            SELECT method, COUNT(*) as count, SUM(amount) as total
            FROM payments
            WHERE $where
            GROUP BY method
        ", $params);

        return $stats;
    }
}
