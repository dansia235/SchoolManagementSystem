<?php
/**
 * User Model
 */
class User {
    /**
     * Get all users
     */
    public static function all() {
        return DB::query('SELECT * FROM users ORDER BY name');
    }

    /**
     * Find user by ID
     */
    public static function find($id) {
        return DB::queryOne('SELECT * FROM users WHERE id = ?', [$id]);
    }

    /**
     * Find user by email
     */
    public static function findByEmail($email) {
        return DB::queryOne('SELECT * FROM users WHERE email = ?', [$email]);
    }

    /**
     * Create new user
     */
    public static function create($data) {
        // Check if email exists
        if (self::findByEmail($data['email'])) {
            flash('error', 'Cette adresse email est déjà utilisée.');
            return false;
        }

        $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

        $sql = '
            INSERT INTO users (name, email, password_hash, role, is_active)
            VALUES (?, ?, ?, ?, ?)
        ';

        DB::execute($sql, [
            $data['name'],
            $data['email'],
            $password_hash,
            $data['role'] ?? 'VIEWER',
            $data['is_active'] ?? 1
        ]);

        $user_id = DB::lastInsertId();
        log_activity('user_created', 'user', $user_id, 'User created: ' . $data['name']);

        return $user_id;
    }

    /**
     * Update user
     */
    public static function update($id, $data) {
        $sql = 'UPDATE users SET name = ?, email = ?, role = ?, is_active = ? WHERE id = ?';

        $result = DB::execute($sql, [
            $data['name'],
            $data['email'],
            $data['role'],
            $data['is_active'] ?? 1,
            $id
        ]);

        if ($result) {
            log_activity('user_updated', 'user', $id, 'User updated');
        }

        return $result;
    }

    /**
     * Update user password
     */
    public static function updatePassword($id, $new_password) {
        $password_hash = password_hash($new_password, PASSWORD_DEFAULT);

        $result = DB::execute('UPDATE users SET password_hash = ? WHERE id = ?', [$password_hash, $id]);

        if ($result) {
            log_activity('password_changed', 'user', $id, 'Password changed');
        }

        return $result;
    }

    /**
     * Delete user
     */
    public static function delete($id) {
        // Prevent deleting yourself
        if ($id == Auth::id()) {
            flash('error', 'Vous ne pouvez pas supprimer votre propre compte.');
            return false;
        }

        $result = DB::execute('DELETE FROM users WHERE id = ?', [$id]);

        if ($result) {
            log_activity('user_deleted', 'user', $id, 'User deleted');
        }

        return $result;
    }

    /**
     * Get users by role
     */
    public static function byRole($role) {
        return DB::query('SELECT * FROM users WHERE role = ? AND is_active = 1 ORDER BY name', [$role]);
    }

    /**
     * Get teachers
     */
    public static function teachers() {
        return self::byRole('TEACHER');
    }

    /**
     * Toggle user active status
     */
    public static function toggleActive($id) {
        return DB::execute('UPDATE users SET is_active = NOT is_active WHERE id = ?', [$id]);
    }
}
