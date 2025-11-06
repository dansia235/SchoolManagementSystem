<?php
/**
 * Fee Model
 */
class Fee {
    /**
     * Get all fees
     */
    public static function all() {
        return DB::query('SELECT * FROM fees WHERE is_active = 1 ORDER BY name');
    }

    /**
     * Find fee by ID
     */
    public static function find($id) {
        return DB::queryOne('SELECT * FROM fees WHERE id = ?', [$id]);
    }

    /**
     * Create new fee
     */
    public static function create($data) {
        $sql = 'INSERT INTO fees (name, description, amount, frequency, is_recurring, is_active) VALUES (?, ?, ?, ?, ?, ?)';

        DB::execute($sql, [
            $data['name'],
            $data['description'] ?? null,
            $data['amount'],
            $data['frequency'] ?? 'YEARLY',
            $data['is_recurring'] ?? 1,
            $data['is_active'] ?? 1
        ]);

        return DB::lastInsertId();
    }

    /**
     * Update fee
     */
    public static function update($id, $data) {
        $sql = 'UPDATE fees SET name = ?, description = ?, amount = ?, frequency = ?, is_recurring = ?, is_active = ? WHERE id = ?';

        return DB::execute($sql, [
            $data['name'],
            $data['description'] ?? null,
            $data['amount'],
            $data['frequency'] ?? 'YEARLY',
            $data['is_recurring'] ?? 1,
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    /**
     * Delete fee
     */
    public static function delete($id) {
        return DB::execute('DELETE FROM fees WHERE id = ?', [$id]);
    }
}
