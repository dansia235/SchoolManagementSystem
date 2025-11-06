<?php
/**
 * Student Model
 */
class Student {
    /**
     * Get all students with pagination
     */
    public static function all($page = 1, $per_page = null) {
        if ($per_page === null) {
            $per_page = ITEMS_PER_PAGE;
        }

        $offset = ($page - 1) * $per_page;

        $sql = '
            SELECT s.*, c.name as class_name
            FROM students s
            LEFT JOIN classes c ON c.id = s.class_id
            ORDER BY s.last_name, s.first_name
            LIMIT ? OFFSET ?
        ';

        return DB::query($sql, [$per_page, $offset]);
    }

    /**
     * Count total students
     */
    public static function count($filters = []) {
        $sql = 'SELECT COUNT(*) FROM students WHERE 1=1';
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= ' AND status = ?';
            $params[] = $filters['status'];
        }

        if (!empty($filters['class_id'])) {
            $sql .= ' AND class_id = ?';
            $params[] = $filters['class_id'];
        }

        return DB::queryValue($sql, $params);
    }

    /**
     * Find student by ID
     */
    public static function find($id) {
        $sql = '
            SELECT s.*, c.name as class_name, c.level as class_level
            FROM students s
            LEFT JOIN classes c ON c.id = s.class_id
            WHERE s.id = ?
        ';

        return DB::queryOne($sql, [$id]);
    }

    /**
     * Find student by matricule
     */
    public static function findByMatricule($matricule) {
        $sql = 'SELECT * FROM students WHERE matricule = ?';
        return DB::queryOne($sql, [$matricule]);
    }

    /**
     * Get students by class
     */
    public static function byClass($class_id) {
        $sql = '
            SELECT s.*
            FROM students s
            WHERE s.class_id = ? AND s.status = "ACTIVE"
            ORDER BY s.last_name, s.first_name
        ';

        return DB::query($sql, [$class_id]);
    }

    /**
     * Search students
     */
    public static function search($query, $filters = []) {
        $sql = '
            SELECT s.*, c.name as class_name
            FROM students s
            LEFT JOIN classes c ON c.id = s.class_id
            WHERE (
                s.first_name LIKE ? OR
                s.last_name LIKE ? OR
                s.matricule LIKE ?
            )
        ';

        $params = ["%$query%", "%$query%", "%$query%"];

        if (!empty($filters['class_id'])) {
            $sql .= ' AND s.class_id = ?';
            $params[] = $filters['class_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= ' AND s.status = ?';
            $params[] = $filters['status'];
        }

        $sql .= ' ORDER BY s.last_name, s.first_name LIMIT 50';

        return DB::query($sql, $params);
    }

    /**
     * Create new student
     */
    public static function create($data) {
        // Generate matricule if not provided
        if (empty($data['matricule'])) {
            $data['matricule'] = generate_matricule();
        }

        $sql = '
            INSERT INTO students (
                matricule, first_name, last_name, sex, birthdate, birthplace,
                class_id, parent_name, parent_phone, parent_email, address,
                photo, status, enrollment_date, notes
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ';

        DB::execute($sql, [
            $data['matricule'],
            $data['first_name'],
            $data['last_name'],
            $data['sex'],
            $data['birthdate'] ?? null,
            $data['birthplace'] ?? null,
            $data['class_id'],
            $data['parent_name'] ?? null,
            $data['parent_phone'] ?? null,
            $data['parent_email'] ?? null,
            $data['address'] ?? null,
            $data['photo'] ?? null,
            $data['status'] ?? 'ACTIVE',
            $data['enrollment_date'] ?? date('Y-m-d'),
            $data['notes'] ?? null
        ]);

        $student_id = DB::lastInsertId();
        log_activity('student_created', 'student', $student_id, 'Student created: ' . $data['first_name'] . ' ' . $data['last_name']);

        return $student_id;
    }

    /**
     * Update student
     */
    public static function update($id, $data) {
        $sql = '
            UPDATE students SET
                first_name = ?,
                last_name = ?,
                sex = ?,
                birthdate = ?,
                birthplace = ?,
                class_id = ?,
                parent_name = ?,
                parent_phone = ?,
                parent_email = ?,
                address = ?,
                photo = ?,
                status = ?,
                notes = ?
            WHERE id = ?
        ';

        $result = DB::execute($sql, [
            $data['first_name'],
            $data['last_name'],
            $data['sex'],
            $data['birthdate'] ?? null,
            $data['birthplace'] ?? null,
            $data['class_id'],
            $data['parent_name'] ?? null,
            $data['parent_phone'] ?? null,
            $data['parent_email'] ?? null,
            $data['address'] ?? null,
            $data['photo'] ?? null,
            $data['status'] ?? 'ACTIVE',
            $data['notes'] ?? null,
            $id
        ]);

        if ($result) {
            log_activity('student_updated', 'student', $id, 'Student updated');
        }

        return $result;
    }

    /**
     * Delete student
     */
    public static function delete($id) {
        $result = DB::execute('DELETE FROM students WHERE id = ?', [$id]);

        if ($result) {
            log_activity('student_deleted', 'student', $id, 'Student deleted');
        }

        return $result;
    }

    /**
     * Get student statistics
     */
    public static function statistics() {
        $stats = [];

        // Total students
        $stats['total'] = DB::queryValue('SELECT COUNT(*) FROM students');

        // Active students
        $stats['active'] = DB::queryValue('SELECT COUNT(*) FROM students WHERE status = "ACTIVE"');

        // Students by sex
        $stats['male'] = DB::queryValue('SELECT COUNT(*) FROM students WHERE sex = "M" AND status = "ACTIVE"');
        $stats['female'] = DB::queryValue('SELECT COUNT(*) FROM students WHERE sex = "F" AND status = "ACTIVE"');

        // Students by class
        $stats['by_class'] = DB::query('
            SELECT c.name, COUNT(s.id) as count
            FROM classes c
            LEFT JOIN students s ON s.class_id = c.id AND s.status = "ACTIVE"
            GROUP BY c.id, c.name
            ORDER BY c.level
        ');

        return $stats;
    }
}
