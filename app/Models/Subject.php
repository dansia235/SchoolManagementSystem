<?php
/**
 * Subject Model
 */
class Subject {
    /**
     * Get all subjects
     */
    public static function all() {
        return DB::query('SELECT * FROM subjects WHERE is_active = 1 ORDER BY name');
    }

    /**
     * Find subject by ID
     */
    public static function find($id) {
        return DB::queryOne('SELECT * FROM subjects WHERE id = ?', [$id]);
    }

    /**
     * Create new subject
     */
    public static function create($data) {
        $sql = 'INSERT INTO subjects (name, code, description, is_active) VALUES (?, ?, ?, ?)';

        DB::execute($sql, [
            $data['name'],
            $data['code'] ?? null,
            $data['description'] ?? null,
            $data['is_active'] ?? 1
        ]);

        return DB::lastInsertId();
    }

    /**
     * Update subject
     */
    public static function update($id, $data) {
        $sql = 'UPDATE subjects SET name = ?, code = ?, description = ?, is_active = ? WHERE id = ?';

        return DB::execute($sql, [
            $data['name'],
            $data['code'] ?? null,
            $data['description'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    /**
     * Delete subject
     */
    public static function delete($id) {
        return DB::execute('DELETE FROM subjects WHERE id = ?', [$id]);
    }
}
