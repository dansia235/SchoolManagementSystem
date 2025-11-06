<?php
/**
 * Invoice Model
 */
class Invoice {
    /**
     * Get all invoices with pagination
     */
    public static function all($page = 1, $per_page = null) {
        if ($per_page === null) {
            $per_page = ITEMS_PER_PAGE;
        }

        $offset = ($page - 1) * $per_page;

        $sql = '
            SELECT i.*, s.first_name, s.last_name, s.matricule,
                   COALESCE(SUM(p.amount), 0) as paid_amount,
                   (i.final_total - COALESCE(SUM(p.amount), 0)) as balance
            FROM invoices i
            JOIN students s ON s.id = i.student_id
            LEFT JOIN payments p ON p.invoice_id = i.id
            GROUP BY i.id
            ORDER BY i.created_at DESC
            LIMIT ? OFFSET ?
        ';

        return DB::query($sql, [$per_page, $offset]);
    }

    /**
     * Count invoices
     */
    public static function count($filters = []) {
        $sql = 'SELECT COUNT(*) FROM invoices WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        return DB::queryValue($sql, $params);
    }

    /**
     * Find invoice by ID with details
     */
    public static function find($id) {
        $sql = '
            SELECT i.*, s.first_name, s.last_name, s.matricule, s.class_id, c.name as class_name,
                   COALESCE(SUM(p.amount), 0) as paid_amount,
                   (i.final_total - COALESCE(SUM(p.amount), 0)) as balance
            FROM invoices i
            JOIN students s ON s.id = i.student_id
            LEFT JOIN classes c ON c.id = s.class_id
            LEFT JOIN payments p ON p.invoice_id = i.id
            WHERE i.id = ?
            GROUP BY i.id
        ';

        return DB::queryOne($sql, [$id]);
    }

    /**
     * Get invoice items
     */
    public static function items($invoice_id) {
        return DB::query('SELECT * FROM invoice_items WHERE invoice_id = ? ORDER BY id', [$invoice_id]);
    }

    /**
     * Get invoice payments
     */
    public static function payments($invoice_id) {
        return DB::query('
            SELECT p.*, u.name as received_by_name
            FROM payments p
            LEFT JOIN users u ON u.id = p.received_by
            WHERE p.invoice_id = ?
            ORDER BY p.paid_at DESC
        ', [$invoice_id]);
    }

    /**
     * Get invoices by student
     */
    public static function byStudent($student_id) {
        $sql = '
            SELECT i.*,
                   COALESCE(SUM(p.amount), 0) as paid_amount,
                   (i.final_total - COALESCE(SUM(p.amount), 0)) as balance
            FROM invoices i
            LEFT JOIN payments p ON p.invoice_id = i.id
            WHERE i.student_id = ?
            GROUP BY i.id
            ORDER BY i.issue_date DESC
        ';

        return DB::query($sql, [$student_id]);
    }

    /**
     * Create invoice
     */
    public static function create($data, $items) {
        DB::beginTransaction();

        try {
            // Calculate totals
            $total = 0;
            foreach ($items as $item) {
                $total += $item['qty'] * $item['unit_price'];
            }

            $discount = $data['discount'] ?? 0;
            $final_total = $total - $discount;

            // Insert invoice
            $sql = '
                INSERT INTO invoices (invoice_number, student_id, issue_date, due_date, total, discount, final_total, status, notes, created_by)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ';

            DB::execute($sql, [
                $data['invoice_number'] ?? generate_invoice_number(),
                $data['student_id'],
                $data['issue_date'],
                $data['due_date'],
                $total,
                $discount,
                $final_total,
                $data['status'] ?? 'DUE',
                $data['notes'] ?? null,
                Auth::id()
            ]);

            $invoice_id = DB::lastInsertId();

            // Insert invoice items
            foreach ($items as $item) {
                $line_total = $item['qty'] * $item['unit_price'];

                DB::execute('
                    INSERT INTO invoice_items (invoice_id, fee_id, description, qty, unit_price, line_total)
                    VALUES (?, ?, ?, ?, ?, ?)
                ', [
                    $invoice_id,
                    $item['fee_id'] ?? null,
                    $item['description'],
                    $item['qty'],
                    $item['unit_price'],
                    $line_total
                ]);
            }

            DB::commit();

            log_activity('invoice_created', 'invoice', $invoice_id, 'Invoice created for student ' . $data['student_id']);

            return $invoice_id;
        } catch (Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    /**
     * Update invoice status
     */
    public static function updateStatus($invoice_id, $status) {
        return DB::execute('UPDATE invoices SET status = ? WHERE id = ?', [$status, $invoice_id]);
    }

    /**
     * Calculate and update invoice status based on payments
     */
    public static function recalculateStatus($invoice_id) {
        $invoice = self::find($invoice_id);

        if (!$invoice) {
            return false;
        }

        $balance = $invoice['balance'];
        $final_total = $invoice['final_total'];

        if ($balance <= 0) {
            $status = 'PAID';
        } elseif ($balance < $final_total) {
            $status = 'PARTIAL';
        } else {
            $status = 'DUE';
        }

        return self::updateStatus($invoice_id, $status);
    }

    /**
     * Get overdue invoices
     */
    public static function overdue() {
        $sql = '
            SELECT i.*, s.first_name, s.last_name, s.matricule,
                   COALESCE(SUM(p.amount), 0) as paid_amount,
                   (i.final_total - COALESCE(SUM(p.amount), 0)) as balance
            FROM invoices i
            JOIN students s ON s.id = i.student_id
            LEFT JOIN payments p ON p.invoice_id = i.id
            WHERE i.due_date < CURDATE() AND i.status IN ("DUE", "PARTIAL")
            GROUP BY i.id
            ORDER BY i.due_date
        ';

        return DB::query($sql);
    }

    /**
     * Get invoice statistics
     */
    public static function statistics() {
        $stats = [];

        // Total invoices
        $stats['total_invoices'] = DB::queryValue('SELECT COUNT(*) FROM invoices');

        // Total billed
        $stats['total_billed'] = DB::queryValue('SELECT SUM(final_total) FROM invoices');

        // Total paid
        $stats['total_paid'] = DB::queryValue('SELECT SUM(amount) FROM payments');

        // Total outstanding
        $stats['total_outstanding'] = $stats['total_billed'] - $stats['total_paid'];

        // Invoices by status
        $stats['by_status'] = DB::query('
            SELECT status, COUNT(*) as count, SUM(final_total) as total
            FROM invoices
            GROUP BY status
        ');

        return $stats;
    }
}
