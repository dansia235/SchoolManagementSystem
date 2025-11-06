<?php
/**
 * Database Connection Singleton
 *
 * Provides a single PDO instance for all database operations
 */
class DB {
    private static $pdo = null;

    /**
     * Get PDO instance
     *
     * @return PDO
     */
    public static function pdo() {
        if (self::$pdo === null) {
            try {
                $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4;port=' . DB_PORT;

                $options = [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
                ];

                self::$pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            } catch (PDOException $e) {
                // Log error
                error_log('DB Connection Error: ' . $e->getMessage());

                if (DEBUG) {
                    die('Database connection failed: ' . $e->getMessage());
                } else {
                    die('Erreur de connexion à la base de données. Veuillez contacter l\'administrateur.');
                }
            }
        }

        return self::$pdo;
    }

    /**
     * Execute a query and return results
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array Results
     */
    public static function query($sql, $params = []) {
        try {
            $st = self::pdo()->prepare($sql);
            $st->execute($params);
            return $st->fetchAll();
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Execute a query and return single row
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return array|false Single row or false
     */
    public static function queryOne($sql, $params = []) {
        try {
            $st = self::pdo()->prepare($sql);
            $st->execute($params);
            return $st->fetch();
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Execute a query and return single value
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return mixed Single value
     */
    public static function queryValue($sql, $params = []) {
        try {
            $st = self::pdo()->prepare($sql);
            $st->execute($params);
            return $st->fetchColumn();
        } catch (PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Execute an insert/update/delete query
     *
     * @param string $sql SQL query
     * @param array $params Parameters for prepared statement
     * @return bool Success status
     */
    public static function execute($sql, $params = []) {
        try {
            $st = self::pdo()->prepare($sql);
            return $st->execute($params);
        } catch (PDOException $e) {
            error_log('Execute Error: ' . $e->getMessage() . ' | SQL: ' . $sql);
            throw $e;
        }
    }

    /**
     * Get last insert ID
     *
     * @return string Last insert ID
     */
    public static function lastInsertId() {
        return self::pdo()->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public static function beginTransaction() {
        return self::pdo()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public static function commit() {
        return self::pdo()->commit();
    }

    /**
     * Rollback transaction
     */
    public static function rollback() {
        return self::pdo()->rollBack();
    }
}
