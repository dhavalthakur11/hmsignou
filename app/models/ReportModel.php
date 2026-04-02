<?php
/**
 * Report Model
 * Aggregated queries for occupancy, revenue, and booking trends.
 */
class ReportModel extends Model {

    /** Occupancy stats */
    public function occupancySummary(): array {
        $r = $this->db->query(
            "SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN status='available'   THEN 1 ELSE 0 END) AS available,
                SUM(CASE WHEN status='booked'       THEN 1 ELSE 0 END) AS booked,
                SUM(CASE WHEN status='maintenance'  THEN 1 ELSE 0 END) AS maintenance
             FROM rooms"
        );
        return $r[0] ?? [];
    }

    /** Monthly revenue for the last 12 months */
    public function monthlyRevenue(): array {
        return $this->db->query(
            "SELECT TO_CHAR(paid_at,'Mon YYYY') AS month,
                    TRUNC(paid_at,'MM')          AS sort_date,
                    SUM(total_amount)            AS revenue,
                    COUNT(*)                     AS paid_bills
               FROM billing
              WHERE payment_status = 'paid'
                AND paid_at >= ADD_MONTHS(SYSDATE,-12)
              GROUP BY TO_CHAR(paid_at,'Mon YYYY'), TRUNC(paid_at,'MM')
              ORDER BY TRUNC(paid_at,'MM') ASC"
        ) ?: [];
    }

    /** Bookings by status (for pie/donut data) */
    public function bookingsByStatus(): array {
        return $this->db->query(
            "SELECT status, COUNT(*) AS cnt
               FROM bookings
              GROUP BY status
              ORDER BY cnt DESC"
        ) ?: [];
    }

    /** Top rooms by booking count */
    public function topRooms(int $limit = 5): array {
        return $this->db->query(
            "SELECT r.room_number, r.room_type, COUNT(b.booking_id) AS bookings,
                    NVL(SUM(bi.total_amount),0) AS revenue
               FROM rooms r
          LEFT JOIN bookings b  ON r.room_id    = b.room_id
          LEFT JOIN billing  bi ON b.booking_id = bi.booking_id
                                AND bi.payment_status = 'paid'
              GROUP BY r.room_number, r.room_type
              ORDER BY bookings DESC
              FETCH FIRST :lim ROWS ONLY",
            ['lim' => $limit]
        ) ?: [];
    }

    /** Revenue summary (today, this month, all time) */
    public function revenueSummary(): array {
        $r = $this->db->query(
            "SELECT
                NVL(SUM(CASE WHEN TRUNC(paid_at)=TRUNC(SYSDATE) THEN total_amount END),0) AS today,
                NVL(SUM(CASE WHEN TRUNC(paid_at,'MM')=TRUNC(SYSDATE,'MM') THEN total_amount END),0) AS this_month,
                NVL(SUM(total_amount),0) AS all_time
             FROM billing WHERE payment_status='paid'"
        );
        return $r[0] ?? ['today' => 0, 'this_month' => 0, 'all_time' => 0];
    }

    /** Booking counts: today, this week, this month */
    public function bookingTrends(): array {
        $r = $this->db->query(
            "SELECT
                SUM(CASE WHEN TRUNC(created_at)=TRUNC(SYSDATE) THEN 1 ELSE 0 END) AS today,
                SUM(CASE WHEN created_at >= TRUNC(SYSDATE,'IW') THEN 1 ELSE 0 END) AS this_week,
                SUM(CASE WHEN TRUNC(created_at,'MM')=TRUNC(SYSDATE,'MM') THEN 1 ELSE 0 END) AS this_month
             FROM bookings WHERE status != 'cancelled'"
        );
        return $r[0] ?? ['today' => 0, 'this_week' => 0, 'this_month' => 0];
    }
}