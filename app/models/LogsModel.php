<?php
/**
 * Logs Model
 * Records all significant system actions for audit/security review.
 */
class LogsModel extends Model {

    /** Insert an audit log entry */
    public function log(string $action, int $userId, string $detail = ''): void {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $this->db->query(
            "INSERT INTO audit_logs (log_id, action, user_id, detail, ip_address, created_at)
             VALUES (audit_logs_seq.NEXTVAL, :action, :user_id, :detail, :ip, SYSDATE)",
            [
                'action'  => $action,
                'user_id' => $userId,
                'detail'  => substr($detail, 0, 500), // Clamp to column size
                'ip'      => $ip,
            ],
            false
        );
    }

    /** Get recent logs (admin view) */
    public function getRecent(int $limit = 100): array {
        return $this->db->query(
            "SELECT l.log_id, l.action, l.detail, l.ip_address, l.created_at,
                    u.name AS user_name
               FROM audit_logs l
               LEFT JOIN users u ON l.user_id = u.user_id
              ORDER BY l.created_at DESC
              FETCH FIRST :limit ROWS ONLY",
            ['limit' => $limit]
        ) ?: [];
    }
    /** Get logs with optional filters */
    public function getFiltered(array $filters = []): array {
        $sql = "SELECT l.log_id, l.action, l.detail, l.ip_address,
                       l.created_at,
                       NVL(u.name, 'System') AS user_name,
                       u.role
                  FROM audit_logs l
             LEFT JOIN users u ON l.user_id = u.user_id
                 WHERE 1=1";
        $params = [];

        if (!empty($filters['action'])) {
            $sql .= " AND l.action = :action";
            $params['action'] = $filters['action'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND TRUNC(l.created_at) >= TO_DATE(:date_from,'YYYY-MM-DD')";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND TRUNC(l.created_at) <= TO_DATE(:date_to,'YYYY-MM-DD')";
            $params['date_to'] = $filters['date_to'];
        }

        $sql .= " ORDER BY l.created_at DESC FETCH FIRST 500 ROWS ONLY";
        return $this->db->query($sql, $params) ?: [];
    }

    /** Distinct action types for filter dropdown */
    public function getDistinctActions(): array {
        $rows = $this->db->query(
            "SELECT DISTINCT action FROM audit_logs ORDER BY action"
        );
        return array_column($rows ?: [], 'action');
    }
}