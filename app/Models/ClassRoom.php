<?php
/**
 * ClassRoom Model (Classes)
 */
class ClassRoom {
    /**
     * Get all classes
     */
    public static function all() {
        return DB::query('
            SELECT c.*, COUNT(s.id) as student_count
            FROM classes c
            LEFT JOIN students s ON s.class_id = c.id AND s.status = "ACTIVE"
            WHERE c.is_active = 1
            GROUP BY c.id
            ORDER BY c.level, c.name
        ');
    }

    /**
     * Find class by ID
     */
    public static function find($id) {
        return DB::queryOne('SELECT * FROM classes WHERE id = ?', [$id]);
    }

    /**
     * Create new class
     */
    public static function create($data) {
        $sql = 'INSERT INTO classes (name, level, capacity, description, is_active) VALUES (?, ?, ?, ?, ?)';

        DB::execute($sql, [
            $data['name'],
            $data['level'],
            $data['capacity'] ?? null,
            $data['description'] ?? null,
            $data['is_active'] ?? 1
        ]);

        return DB::lastInsertId();
    }

    /**
     * Update class
     */
    public static function update($id, $data) {
        $sql = 'UPDATE classes SET name = ?, level = ?, capacity = ?, description = ?, is_active = ? WHERE id = ?';

        return DB::execute($sql, [
            $data['name'],
            $data['level'],
            $data['capacity'] ?? null,
            $data['description'] ?? null,
            $data['is_active'] ?? 1,
            $id
        ]);
    }

    /**
     * Delete class
     */
    public static function delete($id) {
        return DB::execute('DELETE FROM classes WHERE id = ?', [$id]);
    }

    /**
     * Get class subjects with coefficients
     */
    public static function subjects($class_id) {
        return DB::query('
            SELECT s.*, cs.coefficient, cs.id as class_subject_id, u.name as teacher_name
            FROM class_subjects cs
            JOIN subjects s ON s.id = cs.subject_id
            LEFT JOIN users u ON u.id = cs.teacher_id
            WHERE cs.class_id = ?
            ORDER BY s.name
        ', [$class_id]);
    }

    /**
     * Assign subject to class
     */
    public static function assignSubject($class_id, $subject_id, $coefficient = 1.0, $teacher_id = null) {
        $sql = 'INSERT INTO class_subjects (class_id, subject_id, coefficient, teacher_id, academic_year) VALUES (?, ?, ?, ?, ?)';

        return DB::execute($sql, [
            $class_id,
            $subject_id,
            $coefficient,
            $teacher_id,
            academic_year()
        ]);
    }

    /**
     * Update subject coefficient
     */
    public static function updateSubjectCoefficient($class_subject_id, $coefficient, $teacher_id = null) {
        $sql = 'UPDATE class_subjects SET coefficient = ?, teacher_id = ? WHERE id = ?';

        return DB::execute($sql, [$coefficient, $teacher_id, $class_subject_id]);
    }

    /**
     * Remove subject from class
     */
    public static function removeSubject($class_subject_id) {
        return DB::execute('DELETE FROM class_subjects WHERE id = ?', [$class_subject_id]);
    }
}
