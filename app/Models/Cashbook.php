<?php
/**
 * Cashbook Model
 */
class Cashbook {
    /**
     * Get all transactions with pagination
     */
    public static function all($page = 1, $per_page = null, $filters = []) {
        if ($per_page === null) {
            $per_page = ITEMS_PER_PAGE;
        }

        $offset = ($page - 1) * $per_page;

        $where = '1=1';
        $params = [];

        if (!empty($filters['kind'])) {
            $where .= ' AND kind = ?';
            $params[] = $filters['kind'];
        }

        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $where .= ' AND DATE(at) BETWEEN ? AND ?';
            $params[] = $filters['start_date'];
            $params[] = $filters['end_date'];
        }

        $sql = "
            SELECT c.*, u.name as user_name
            FROM cashbook c
            LEFT JOIN users u ON u.id = c.user_id
            WHERE $where
            ORDER BY c.at DESC
            LIMIT ? OFFSET ?
        ";

        $params[] = $per_page;
        $params[] = $offset;

        return DB::query($sql, $params);
    }

    /**
     * Count transactions
     */
    public static function count($filters = []) {
        $where = '1=1';
        $params = [];

        if (!empty($filters['kind'])) {
            $where .= ' AND kind = ?';
            $params[] = $filters['kind'];
        }

        return DB::queryValue("SELECT COUNT(*) FROM cashbook WHERE $where", $params);
    }

    /**
     * Find transaction by ID
     */
    public static function find($id) {
        return DB::queryOne('SELECT * FROM cashbook WHERE id = ?', [$id]);
    }

    /**
     * Add income transaction
     */
    public static function addIncome($category, $description, $amount, $method = 'CASH', $reference = null) {
        $sql = '
            INSERT INTO cashbook (transaction_number, kind, category, description, amount, payment_method, reference, at, user_id)
            VALUES (?, "INCOME", ?, ?, ?, ?, ?, ?, ?)
        ';

        DB::execute($sql, [
            generate_transaction_number(),
            $category,
            $description,
            abs($amount), // Ensure positive
            $method,
            $reference,
            date('Y-m-d H:i:s'),
            Auth::id()
        ]);

        $id = DB::lastInsertId();
        log_activity('cashbook_income', 'cashbook', $id, "Income: $amount");

        return $id;
    }

    /**
     * Add expense transaction
     */
    public static function addExpense($category, $description, $amount, $method = 'CASH', $reference = null) {
        $sql = '
            INSERT INTO cashbook (transaction_number, kind, category, description, amount, payment_method, reference, at, user_id)
            VALUES (?, "EXPENSE", ?, ?, ?, ?, ?, ?, ?)
        ';

        DB::execute($sql, [
            generate_transaction_number(),
            $category,
            $description,
            abs($amount), // Ensure positive
            $method,
            $reference,
            date('Y-m-d H:i:s'),
            Auth::id()
        ]);

        $id = DB::lastInsertId();
        log_activity('cashbook_expense', 'cashbook', $id, "Expense: $amount");

        return $id;
    }

    /**
     * Delete transaction
     */
    public static function delete($id) {
        $result = DB::execute('DELETE FROM cashbook WHERE id = ?', [$id]);

        if ($result) {
            log_activity('cashbook_deleted', 'cashbook', $id, 'Transaction deleted');
        }

        return $result;
    }

    /**
     * Get balance
     */
    public static function balance($start_date = null, $end_date = null) {
        $where = '1=1';
        $params = [];

        if ($start_date && $end_date) {
            $where = 'DATE(at) BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }

        $sql = "
            SELECT
                SUM(CASE WHEN kind = 'INCOME' THEN amount ELSE 0 END) as total_income,
                SUM(CASE WHEN kind = 'EXPENSE' THEN amount ELSE 0 END) as total_expense
            FROM cashbook
            WHERE $where
        ";

        $result = DB::queryOne($sql, $params);

        $income = (float)($result['total_income'] ?? 0);
        $expense = (float)($result['total_expense'] ?? 0);

        return [
            'income' => $income,
            'expense' => $expense,
            'balance' => $income - $expense
        ];
    }

    /**
     * Get statistics
     */
    public static function statistics($start_date = null, $end_date = null) {
        $where = '1=1';
        $params = [];

        if ($start_date && $end_date) {
            $where = 'DATE(at) BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }

        $stats = self::balance($start_date, $end_date);

        // By category
        $stats['by_category'] = DB::query("
            SELECT kind, category, SUM(amount) as total, COUNT(*) as count
            FROM cashbook
            WHERE $where
            GROUP BY kind, category
            ORDER BY kind, total DESC
        ", $params);

        // By payment method
        $stats['by_method'] = DB::query("
            SELECT kind, payment_method, SUM(amount) as total, COUNT(*) as count
            FROM cashbook
            WHERE $where
            GROUP BY kind, payment_method
        ", $params);

        // Recent transactions
        $stats['recent'] = DB::query("
            SELECT *
            FROM cashbook
            WHERE $where
            ORDER BY at DESC
            LIMIT 10
        ", $params);

        return $stats;
    }

    /**
     * Export to CSV
     */
    public static function exportCSV($start_date = null, $end_date = null) {
        $where = '1=1';
        $params = [];

        if ($start_date && $end_date) {
            $where = 'DATE(at) BETWEEN ? AND ?';
            $params = [$start_date, $end_date];
        }

        $transactions = DB::query("
            SELECT transaction_number, at, kind, category, description, amount, payment_method, reference
            FROM cashbook
            WHERE $where
            ORDER BY at DESC
        ", $params);

        $filename = 'cashbook_' . date('Y-m-d_His') . '.csv';
        $filepath = EXPORTS_PATH . '/' . $filename;

        $fp = fopen($filepath, 'w');

        // Write BOM for UTF-8
        fprintf($fp, chr(0xEF).chr(0xBB).chr(0xBF));

        // Headers
        fputcsv($fp, ['N° Transaction', 'Date', 'Type', 'Catégorie', 'Description', 'Montant', 'Mode de paiement', 'Référence']);

        // Data
        foreach ($transactions as $t) {
            fputcsv($fp, [
                $t['transaction_number'],
                $t['at'],
                $t['kind'] === 'INCOME' ? 'Entrée' : 'Sortie',
                $t['category'],
                $t['description'],
                $t['amount'],
                $t['payment_method'],
                $t['reference']
            ]);
        }

        fclose($fp);

        return $filename;
    }
}
