<?php
/**
 * Booking Model
 * Handles all booking DB operations including availability,
 * creation, status updates, and reporting queries.
 */
class BookingModel extends Model {

    /**
     * Get all bookings with joined guest + room data.
     * Supports filters: status, room_id, user_id, date range.
     */
    public function getAll(array $filters = []): array {
        $sql = "SELECT b.booking_id, b.check_in, b.check_out, b.guests,
                       b.status, b.special_req, b.created_at,
                       u.name  AS guest_name,  u.email AS guest_email,
                       u.phone AS guest_phone,
                       r.room_number, r.room_type, r.price_per_night,
                       s.name  AS booked_by_name
                  FROM bookings b
                  JOIN users u ON b.user_id  = u.user_id
                  JOIN rooms r ON b.room_id  = r.room_id
             LEFT JOIN users s ON b.booked_by = s.user_id
                 WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND b.status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['user_id'])) {
            $sql .= " AND b.user_id = :user_id";
            $params['user_id'] = (int) $filters['user_id'];
        }
        if (!empty($filters['room_id'])) {
            $sql .= " AND b.room_id = :room_id";
            $params['room_id'] = (int) $filters['room_id'];
        }
        if (!empty($filters['date_from'])) {
            $sql .= " AND b.check_in >= TO_DATE(:date_from, 'YYYY-MM-DD')";
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $sql .= " AND b.check_in <= TO_DATE(:date_to, 'YYYY-MM-DD')";
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $sql .= " AND (LOWER(u.name) LIKE LOWER(:search)
                       OR  LOWER(r.room_number) LIKE LOWER(:search2))";
            $params['search']  = '%' . $filters['search'] . '%';
            $params['search2'] = '%' . $filters['search'] . '%';
        }

        $sql .= " ORDER BY b.created_at DESC";
        return $this->db->query($sql, $params) ?: [];
    }

    /** Single booking with all joined data */
    public function findById(int $id): ?array {
        $result = $this->db->query(
            "SELECT b.*, u.name AS guest_name, u.email AS guest_email,
                    u.phone AS guest_phone,
                    r.room_number, r.room_type, r.price_per_night,
                    r.floor, r.capacity, r.amenities
               FROM bookings b
               JOIN users u ON b.user_id = u.user_id
               JOIN rooms r ON b.room_id = r.room_id
              WHERE b.booking_id = :id",
            ['id' => $id]
        );
        return $result[0] ?? null;
    }

    /** Count all bookings */
    public function countAll(): int {
        $r = $this->db->query("SELECT COUNT(*) AS cnt FROM bookings");
        return (int)($r[0]['cnt'] ?? 0);
    }

    /** Count bookings by status */
    public function countByStatus(string $status): int {
        $r = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM bookings WHERE status = :status",
            ['status' => $status]
        );
        return (int)($r[0]['cnt'] ?? 0);
    }

    /** Get N most recent bookings (for dashboard) */
    public function getRecent(int $limit = 10): array {
        return $this->db->query(
            "SELECT b.booking_id, b.check_in, b.check_out, b.status,
                    u.name  AS guest_name,
                    r.room_number
               FROM bookings b
               JOIN users u ON b.user_id = u.user_id
               JOIN rooms r ON b.room_id = r.room_id
              ORDER BY b.created_at DESC
              FETCH FIRST :lim ROWS ONLY",
            ['lim' => $limit]
        ) ?: [];
    }

    /** Get all bookings for a specific customer */
    public function getByUser(int $userId): array {
        return $this->getAll(['user_id' => $userId]);
    }

    /**
     * Create a new booking.
     * Returns the new booking_id on success, 0 on failure.
     */
    public function create(array $d): int {
        $ok = $this->db->query(
            "INSERT INTO bookings
                (booking_id, user_id, room_id, check_in, check_out,
                 guests, status, special_req, booked_by, created_at)
             VALUES
                (bookings_seq.NEXTVAL, :user_id, :room_id,
                 TO_DATE(:check_in,  'YYYY-MM-DD'),
                 TO_DATE(:check_out, 'YYYY-MM-DD'),
                 :guests, 'confirmed', :special_req, :booked_by, SYSDATE)",
            [
                'user_id'     => (int) $d['user_id'],
                'room_id'     => (int) $d['room_id'],
                'check_in'    => $d['check_in'],
                'check_out'   => $d['check_out'],
                'guests'      => (int) $d['guests'],
                'special_req' => $this->sanitize($d['special_req'] ?? ''),
                'booked_by'   => (int) $d['booked_by'],
            ],
            false
        );

        if (!$ok) return 0;
        return $this->db->lastInsertId('bookings_seq');
    }

    /** Update booking status */
    public function updateStatus(int $id, string $status): bool {
        return (bool) $this->db->query(
            "UPDATE bookings SET status = :status WHERE booking_id = :id",
            ['status' => $status, 'id' => $id],
            false
        );
    }

    /** Cancel a booking (also frees the room) */
    public function cancel(int $id): bool {
        return $this->updateStatus($id, 'cancelled');
    }

    /**
     * Check-in: mark booking as checked_in, update room to booked.
     * Both changes in the same logical operation.
     */
    public function checkIn(int $bookingId, int $roomId): bool {
        $b = $this->updateStatus($bookingId, 'checked_in');
        if ($b) {
            $this->db->query(
                "UPDATE rooms SET status = 'booked' WHERE room_id = :id",
                ['id' => $roomId],
                false
            );
        }
        return $b;
    }

    /**
     * Check-out: mark booking as checked_out, free the room.
     */
    public function checkOut(int $bookingId, int $roomId): bool {
        $b = $this->updateStatus($bookingId, 'checked_out');
        if ($b) {
            $this->db->query(
                "UPDATE rooms SET status = 'available' WHERE room_id = :id",
                ['id' => $roomId],
                false
            );
        }
        return $b;
    }

    /**
     * Calculate the number of nights between two date strings.
     */
    public function calcNights(string $checkIn, string $checkOut): int {
        $ci = new DateTime($checkIn);
        $co = new DateTime($checkOut);
        return max(1, (int) $ci->diff($co)->days);
    }

    /**
     * Get today's arrivals (for receptionist dashboard).
     */
    public function getTodayArrivals(): array {
        return $this->db->query(
            "SELECT b.booking_id, b.check_in, b.check_out, b.guests, b.status,
                    u.name AS guest_name, u.phone AS guest_phone,
                    r.room_number, r.room_type
               FROM bookings b
               JOIN users u ON b.user_id = u.user_id
               JOIN rooms r ON b.room_id = r.room_id
              WHERE TRUNC(b.check_in) = TRUNC(SYSDATE)
                AND b.status IN ('confirmed','checked_in')
              ORDER BY b.check_in ASC"
        ) ?: [];
    }

    /**
     * Get today's departures.
     */
    public function getTodayDepartures(): array {
        return $this->db->query(
            "SELECT b.booking_id, b.check_in, b.check_out, b.guests, b.status,
                    u.name AS guest_name, u.phone AS guest_phone,
                    r.room_number, r.room_type
               FROM bookings b
               JOIN users u ON b.user_id = u.user_id
               JOIN rooms r ON b.room_id = r.room_id
              WHERE TRUNC(b.check_out) = TRUNC(SYSDATE)
                AND b.status = 'checked_in'
              ORDER BY b.check_out ASC"
        ) ?: [];
    }
}