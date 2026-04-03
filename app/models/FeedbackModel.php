<?php
/**
 * Feedback Model
 */
class FeedbackModel extends Model {

    public function create(array $d): bool {
        return (bool) $this->db->query(
            "INSERT INTO feedback
                (feedback_id, user_id, booking_id, rating, feedback_comment, created_at)
             VALUES
                (feedback_seq.NEXTVAL, :user_id, :booking_id, :rating, :feedback_text, SYSDATE)",
            [
                'user_id'       => (int)($d['user_id'] ?? 0),
                'booking_id'    => (int)($d['booking_id'] ?? 0),
                'rating'        => (int)($d['rating'] ?? 0),
                'feedback_text' => $this->sanitize($d['comment'] ?? ''),
            ],
            false
        );
    }

    public function getAll(): array {
        return $this->db->query(
            "SELECT
                f.feedback_id,
                f.rating,
                f.feedback_comment AS feedback_text,
                f.created_at,
                u.name AS guest_name,
                b.booking_id,
                r.room_number
             FROM feedback f
             INNER JOIN users u ON f.user_id = u.user_id
             LEFT JOIN bookings b ON f.booking_id = b.booking_id
             LEFT JOIN rooms r ON b.room_id = r.room_id
             ORDER BY f.created_at DESC"
        ) ?: [];
    }

    public function getByUser(int $userId): array {
        return $this->db->query(
            "SELECT
                f.feedback_id,
                f.rating,
                f.feedback_comment AS feedback_text,
                f.created_at,
                r.room_number
             FROM feedback f
             LEFT JOIN bookings b ON f.booking_id = b.booking_id
             LEFT JOIN rooms r ON b.room_id = r.room_id
             WHERE f.user_id = :user_id
             ORDER BY f.created_at DESC",
            ['user_id' => $userId]
        ) ?: [];
    }

    public function getAverageRating(): float {
        $r = $this->db->query(
            "SELECT NVL(ROUND(AVG(rating), 1), 0) AS avg_rating FROM feedback"
        );

        return (float)($r[0]['avg_rating'] ?? 0);
    }
}