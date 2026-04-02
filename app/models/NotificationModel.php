<?php
/**
 * Notification Model
 * In-app notifications for booking events.
 */
class NotificationModel extends Model {

    /** Send a notification to a user */
    public function send(int $userId, string $title, string $message): bool {
        return (bool) $this->db->query(
            "INSERT INTO notifications
                (notif_id, user_id, title, message, is_read, created_at)
             VALUES
                (notifications_seq.NEXTVAL, :user_id, :title, :message, 0, SYSDATE)",
            [
                'user_id' => $userId,
                'title'   => substr($title, 0, 150),
                'message' => substr($message, 0, 500),
            ],
            false
        );
    }

    /** Get notifications for a user */
    public function getForUser(int $userId, bool $unreadOnly = false): array {
        $sql = "SELECT * FROM notifications WHERE user_id = :user_id";
        if ($unreadOnly) {
            $sql .= " AND is_read = 0";
        }
        $sql .= " ORDER BY created_at DESC FETCH FIRST 50 ROWS ONLY";

        return $this->db->query($sql, ['user_id' => $userId]) ?: [];
    }

    /** Count unread notifications */
    public function countUnread(int $userId): int {
        $r = $this->db->query(
            "SELECT COUNT(*) AS cnt
               FROM notifications
              WHERE user_id = :user_id
                AND is_read = 0",
            ['user_id' => $userId]
        );

        return (int)($r[0]['cnt'] ?? 0);
    }

    /** Mark all as read for user */
    public function markAllRead(int $userId): bool {
        return (bool) $this->db->query(
            "UPDATE notifications
                SET is_read = 1
              WHERE user_id = :user_id
                AND is_read = 0",
            ['user_id' => $userId],
            false
        );
    }
}