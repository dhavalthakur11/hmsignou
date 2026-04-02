<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Customer Controller
 * Self-service portal: dashboard, booking history, profile.
 */
class CustomerController extends Controller {

    public function __construct() {
        AuthMiddleware::requireAuth();
    }

    /** GET /customer/dashboard */
    public function dashboard(): void {
        AuthMiddleware::requireRole('customer');

        $bookingModel = $this->model('Booking');
        $notifModel   = $this->model('Notification');
        $roomModel    = $this->model('Room');

        $myBookings = $bookingModel->getByUser(user_id());

        // Segment bookings by status for summary cards
        $active    = array_filter($myBookings,
            fn($b) => in_array($b['status'], ['confirmed', 'checked_in'], true));
        $history   = array_filter($myBookings,
            fn($b) => in_array($b['status'], ['checked_out', 'cancelled'], true));

        $data = [
            'page_title'          => 'My Dashboard',
            'my_bookings'         => array_slice($myBookings, 0, 5),   // Recent 5
            'total_bookings'      => count($myBookings),
            'active_bookings'     => count($active),
            'completed_bookings'  => count(array_filter($myBookings,
                                        fn($b) => $b['status'] === 'checked_out')),
            'notifications'       => $notifModel->getForUser(user_id()),
            'unread_notifs'       => $notifModel->countUnread(user_id()),
            'featured_rooms'      => $roomModel->getAll(['status' => 'available']),
        ];

        // Mark notifications as read when visiting dashboard
        $notifModel->markAllRead(user_id());

        $this->view('customer/dashboard', $data);
    }

    /** GET /customer/list — Admin view of all customers */
    public function list(): void {
        AuthMiddleware::requireStaff();

        $userModel = $this->model('User');
        $this->view('customer/list', [
            'page_title' => 'Customers',
            'customers'  => $userModel->getAll('customer'),
        ]);
    }
}