<?php
/**
 * Billing Model
 * Invoice generation, payment tracking, revenue queries.
 */
class BillingModel extends Model {

    /** Find bill by booking ID */
    public function findByBooking(int $bookingId): ?array {
        $result = $this->db->query(
            "SELECT bi.*, b.check_in, b.check_out, b.guests,
                    u.name AS guest_name, u.email AS guest_email,
                    u.phone AS guest_phone,
                    r.room_number, r.room_type, r.price_per_night
               FROM billing bi
               JOIN bookings b ON bi.booking_id = b.booking_id
               JOIN users   u ON b.user_id      = u.user_id
               JOIN rooms   r ON b.room_id      = r.room_id
              WHERE bi.booking_id = :booking_id",
            ['booking_id' => $bookingId]
        );
        return $result[0] ?? null;
    }

    /** Find bill by bill_id */
    public function findById(int $id): ?array {
        $result = $this->db->query(
            "SELECT bi.*, b.check_in, b.check_out, b.guests,
                    u.name AS guest_name, u.email AS guest_email,
                    u.phone AS guest_phone,
                    r.room_number, r.room_type, r.price_per_night
               FROM billing bi
               JOIN bookings b ON bi.booking_id = b.booking_id
               JOIN users   u ON b.user_id      = u.user_id
               JOIN rooms   r ON b.room_id      = r.room_id
              WHERE bi.bill_id = :id",
            ['id' => $id]
        );
        return $result[0] ?? null;
    }

    /**
     * Auto-generate a bill for a booking.
     * TAX_RATE = 18% GST. Extra charges default to 0.
     */
    public function generate(int $bookingId, float $roomCharges, float $extraCharges = 0): int {
        $taxRate   = 0.18;
        $taxAmount = ($roomCharges + $extraCharges) * $taxRate;
        $total     = $roomCharges + $extraCharges + $taxAmount;

        $ok = $this->db->query(
            "INSERT INTO billing
                (bill_id, booking_id, room_charges, extra_charges,
                 tax_amount, total_amount, payment_status, created_at)
             VALUES
                (billing_seq.NEXTVAL, :booking_id, :room_charges, :extra_charges,
                 :tax_amount, :total_amount, 'pending', SYSDATE)",
            [
                'booking_id'    => $bookingId,
                'room_charges'  => $roomCharges,
                'extra_charges' => $extraCharges,
                'tax_amount'    => round($taxAmount, 2),
                'total_amount'  => round($total, 2),
            ],
            false
        );

        if (!$ok) return 0;
        return $this->db->lastInsertId('billing_seq');
    }

    /** Mark a bill as paid */
    public function markPaid(int $billId, string $method): bool {
        return (bool) $this->db->query(
            "UPDATE billing
                SET payment_status = 'paid',
                    payment_method = :method,
                    paid_at        = SYSDATE
              WHERE bill_id = :id",
            ['method' => $method, 'id' => $billId],
            false
        );
    }

    /** Update extra charges and recalculate totals */
    public function updateExtras(int $billId, float $extraCharges, string $notes = ''): bool {
        // Fetch current room charges
        $bill = $this->db->query(
            "SELECT room_charges FROM billing WHERE bill_id = :id",
            ['id' => $billId]
        );
        if (empty($bill)) return false;

        $roomCharges = (float) $bill[0]['room_charges'];
        $taxAmount   = ($roomCharges + $extraCharges) * 0.18;
        $total       = $roomCharges + $extraCharges + $taxAmount;

        return (bool) $this->db->query(
            "UPDATE billing
                SET extra_charges = :extra,
                    tax_amount    = :tax,
                    total_amount  = :total,
                    notes         = :notes
              WHERE bill_id = :id",
            [
                'extra' => $extraCharges,
                'tax'   => round($taxAmount, 2),
                'total' => round($total, 2),
                'notes' => $notes,
                'id'    => $billId,
            ],
            false
        );
    }

    /** Get all bills (for admin billing index) */
    public function getAll(array $filters = []): array {
        $sql = "SELECT bi.bill_id, bi.total_amount, bi.payment_status,
                       bi.payment_method, bi.paid_at, bi.created_at,
                       b.booking_id, b.check_in, b.check_out,
                       u.name AS guest_name,
                       r.room_number
                  FROM billing bi
                  JOIN bookings b ON bi.booking_id = b.booking_id
                  JOIN users   u ON b.user_id      = u.user_id
                  JOIN rooms   r ON b.room_id      = r.room_id
                 WHERE 1=1";
        $params = [];

        if (!empty($filters['payment_status'])) {
            $sql .= " AND bi.payment_status = :ps";
            $params['ps'] = $filters['payment_status'];
        }

        $sql .= " ORDER BY bi.created_at DESC";
        return $this->db->query($sql, $params) ?: [];
    }

    /** Revenue earned today */
    public function revenueToday(): float {
        $r = $this->db->query(
            "SELECT NVL(SUM(total_amount), 0) AS rev
               FROM billing
              WHERE payment_status = 'paid'
                AND TRUNC(paid_at) = TRUNC(SYSDATE)"
        );
        return (float)($r[0]['rev'] ?? 0);
    }

    /** Revenue earned this calendar month */
    public function revenueThisMonth(): float {
        $r = $this->db->query(
            "SELECT NVL(SUM(total_amount), 0) AS rev
               FROM billing
              WHERE payment_status = 'paid'
                AND TRUNC(paid_at, 'MM') = TRUNC(SYSDATE, 'MM')"
        );
        return (float)($r[0]['rev'] ?? 0);
    }

    /** Monthly revenue for the past 12 months (for charts) */
    public function monthlyRevenue(): array {
        return $this->db->query(
            "SELECT TO_CHAR(paid_at, 'Mon YYYY') AS month,
                    SUM(total_amount)              AS revenue
               FROM billing
              WHERE payment_status = 'paid'
                AND paid_at >= ADD_MONTHS(SYSDATE, -12)
              GROUP BY TO_CHAR(paid_at, 'Mon YYYY'),
                       TRUNC(paid_at, 'MM')
              ORDER BY TRUNC(paid_at, 'MM') ASC"
        ) ?: [];
    }
}