<?php
/**
 * Room Model
 * All DB operations for the rooms table.
 */
class RoomModel extends Model {

    /** Get all rooms with optional filters */
    public function getAll(array $filters = []): array {
        $sql    = "SELECT * FROM rooms WHERE 1=1";
        $params = [];

        if (!empty($filters['status'])) {
            $sql .= " AND status = :status";
            $params['status'] = $filters['status'];
        }
        if (!empty($filters['room_type'])) {
            $sql .= " AND room_type = :room_type";
            $params['room_type'] = $filters['room_type'];
        }
        if (!empty($filters['floor'])) {
            $sql .= " AND floor = :floor";
            $params['floor'] = (int) $filters['floor'];
        }

        $sql .= " ORDER BY room_number ASC";
        return $this->db->query($sql, $params) ?: [];
    }

    /** Find single room by ID */
    public function findById(int $id): ?array {
        $result = $this->db->query(
            "SELECT * FROM rooms WHERE room_id = :id",
            ['id' => $id]
        );
        return $result[0] ?? null;
    }

    /** Find room by room number */
    public function findByNumber(string $number): ?array {
        $result = $this->db->query(
            "SELECT * FROM rooms WHERE room_number = :number",
            ['number' => $number]
        );
        return $result[0] ?? null;
    }

    /** Total count of rooms */
    public function count(): int {
        $result = $this->db->query("SELECT COUNT(*) AS cnt FROM rooms");
        return (int)($result[0]['cnt'] ?? 0);
    }

    /** Count rooms by status */
    public function countByStatus(string $status): int {
        $result = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM rooms WHERE status = :status",
            ['status' => $status]
        );
        return (int)($result[0]['cnt'] ?? 0);
    }

    /** Get distinct room types for filter dropdowns */
    public function getRoomTypes(): array {
        $rows = $this->db->query(
            "SELECT DISTINCT room_type FROM rooms ORDER BY room_type"
        );
        return array_column($rows ?: [], 'room_type');
    }

    /** Create a new room */
    public function create(array $d): bool {
        return (bool) $this->db->query(
            "INSERT INTO rooms
                (room_id, room_number, room_type, floor, capacity,
                 price_per_night, status, description, amenities, created_at)
             VALUES
                (rooms_seq.NEXTVAL, :room_number, :room_type, :floor, :capacity,
                 :price_per_night, :status, :description, :amenities, SYSDATE)",
            [
                'room_number'     => $this->sanitize($d['room_number']),
                'room_type'       => $this->sanitize($d['room_type']),
                'floor'           => (int) $d['floor'],
                'capacity'        => (int) $d['capacity'],
                'price_per_night' => (float) $d['price_per_night'],
                'status'          => $d['status'] ?? 'available',
                'description'     => $this->sanitize($d['description'] ?? ''),
                'amenities'       => $this->sanitize($d['amenities'] ?? ''),
            ],
            false
        );
    }

    /** Update an existing room */
    public function update(int $id, array $d): bool {
        return (bool) $this->db->query(
            "UPDATE rooms SET
                room_number     = :room_number,
                room_type       = :room_type,
                floor           = :floor,
                capacity        = :capacity,
                price_per_night = :price_per_night,
                status          = :status,
                description     = :description,
                amenities       = :amenities
             WHERE room_id = :id",
            [
                'room_number'     => $this->sanitize($d['room_number']),
                'room_type'       => $this->sanitize($d['room_type']),
                'floor'           => (int) $d['floor'],
                'capacity'        => (int) $d['capacity'],
                'price_per_night' => (float) $d['price_per_night'],
                'status'          => $d['status'],
                'description'     => $this->sanitize($d['description'] ?? ''),
                'amenities'       => $this->sanitize($d['amenities'] ?? ''),
                'id'              => $id,
            ],
            false
        );
    }

    /** Update only the status of a room */
    public function updateStatus(int $id, string $status): bool {
        return (bool) $this->db->query(
            "UPDATE rooms SET status = :status WHERE room_id = :id",
            ['status' => $status, 'id' => $id],
            false
        );
    }

    /** Delete a room (only if never booked) */
    public function delete(int $id): bool {
        return (bool) $this->db->query(
            "DELETE FROM rooms WHERE room_id = :id",
            ['id' => $id],
            false
        );
    }

    /**
     * Check if a room is available for a date range.
     * Excludes cancelled bookings from the conflict check.
     */
    public function isAvailable(int $roomId, string $checkIn, string $checkOut, int $excludeBookingId = 0): bool {
        $result = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM bookings
              WHERE room_id   = :room_id
                AND status   != 'cancelled'
                AND booking_id != :excl
                AND check_in  < TO_DATE(:check_out, 'YYYY-MM-DD')
                AND check_out > TO_DATE(:check_in,  'YYYY-MM-DD')",
            [
                'room_id'    => $roomId,
                'excl'       => $excludeBookingId,
                'check_out'  => $checkOut,
                'check_in'   => $checkIn,
            ]
        );
        return (int)($result[0]['cnt'] ?? 1) === 0;
    }

    /** Get available rooms for a given date range */
    public function getAvailable(string $checkIn, string $checkOut): array {
        return $this->db->query(
            "SELECT r.* FROM rooms r
              WHERE r.status = 'available'
                AND r.room_id NOT IN (
                    SELECT b.room_id FROM bookings b
                     WHERE b.status   != 'cancelled'
                       AND b.check_in  < TO_DATE(:co2, 'YYYY-MM-DD')
                       AND b.check_out > TO_DATE(:ci2, 'YYYY-MM-DD')
                )
              ORDER BY r.price_per_night ASC",
            ['co2' => $checkOut, 'ci2' => $checkIn]
        ) ?: [];
    }

    /** Check whether a room number already exists (for uniqueness validation) */
    public function numberExists(string $number, int $excludeId = 0): bool {
        $result = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM rooms
              WHERE room_number = :number AND room_id != :excl",
            ['number' => $number, 'excl' => $excludeId]
        );
        return (int)($result[0]['cnt'] ?? 0) > 0;
    }

    /** Check whether a room has ever been booked (prevents deletion) */
    public function hasBookings(int $id): bool {
        $result = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM bookings WHERE room_id = :id",
            ['id' => $id]
        );
        return (int)($result[0]['cnt'] ?? 0) > 0;
    }
}