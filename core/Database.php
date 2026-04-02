<?php
/**
 * All models inherit or call Database::getInstance().
 */
class Database {
    private static ?Database $instance = null;
    private        $connection;

    private function __construct() {
        $config = require BASE_PATH . '/config/database.php';

        $this->connection = oci_connect(
            $config['username'],
            $config['password'],
            $config['dsn'],
            'AL32UTF8'
        );

        if (!$this->connection) {
            $e = oci_error();
            // Log and show a safe message — never expose credentials
            error_log('[DB] Connection failed: ' . $e['message']);
            die('Database connection failed. Please contact the administrator.');
        }
    }

    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    /** Returns the raw OCI8 connection resource */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Execute a prepared statement with bind variables.
     *
     * @param string $sql    SQL with :placeholder bind variables
     * @param array  $params ['placeholder' => value, ...]
     * @param bool   $fetch  true = return rows, false = DML (insert/update/delete)
     */
    public function query(string $sql, array $params = [], bool $fetch = true): array|bool {
        $stmt = oci_parse($this->connection, $sql);

        if (!$stmt) {
            $e = oci_error($this->connection);
            error_log('[DB] Parse error: ' . $e['message'] . ' | SQL: ' . $sql);
            return false;
        }

        // Bind every parameter — prevents SQL injection
        foreach ($params as $key => &$val) {
        $bindName = ':' . ltrim((string)$key, ':');
    
        if (!preg_match('/^:[A-Za-z_][A-Za-z0-9_]*$/', $bindName)) {
            error_log('[DB] Invalid bind variable name: ' . $bindName . ' | SQL: ' . $sql);
            return false;
        }

    oci_bind_by_name($stmt, $bindName, $val);
}
unset($val);

        $exec = oci_execute($stmt, OCI_DEFAULT); // OCI_DEFAULT = manual commit

        if (!$exec) {
            $e = oci_error($stmt);
            error_log('[DB] Execute error: ' . $e['message']);
            oci_rollback($this->connection);
            return false;
        }

        if ($fetch) {
            $rows = [];
            while ($row = oci_fetch_assoc($stmt)) {
                // Lowercase keys for consistent access in PHP
                $rows[] = array_change_key_case($row, CASE_LOWER);
            }
            oci_free_statement($stmt);
            return $rows;
        }

        // DML — commit and return true
        oci_commit($this->connection);
        oci_free_statement($stmt);
        return true;
    }

    /** Get the last sequence value (Oracle equivalent of lastInsertId) */
    public function lastInsertId(string $sequence): int {
        $result = $this->query("SELECT {$sequence}.CURRVAL AS id FROM DUAL");
        return (int) ($result[0]['id'] ?? 0);
    }

    // Prevent cloning/unserialization of singleton
    private function __clone() {}
    public function __wakeup(): void { throw new \Exception('Cannot unserialize singleton.'); }
}