<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Admin Controller
 * Dashboard + overview stats. All methods require admin role.
 */
class AdminController extends Controller {

    public function __construct() {
        AuthMiddleware::requireAdmin();
    }

    /** GET /admin/dashboard */
    public function dashboard(): void {
        $roomModel    = $this->model('Room');
        $bookingModel = $this->model('Booking');
        $userModel    = $this->model('User');
        $billingModel = $this->model('Billing');

        $data = [
            'page_title'       => 'Admin Dashboard',
            'total_rooms'      => $roomModel->count(),
            'available_rooms'  => $roomModel->countByStatus('available'),
            'booked_rooms'     => $roomModel->countByStatus('booked'),
            'total_bookings'   => $bookingModel->countAll(),
            'active_bookings'  => $bookingModel->countByStatus('confirmed'),
            'total_customers'  => $userModel->countByRole('customer'),
            'total_employees'  => $userModel->countByRole('receptionist'),
            'revenue_today'    => $billingModel->revenueToday(),
            'revenue_month'    => $billingModel->revenueThisMonth(),
            'recent_bookings'  => $bookingModel->getRecent(8),
            'recent_logs'      => $this->model('Logs')->getRecent(6),
        ];

        $this->view('admin/dashboard', $data);
    }
}