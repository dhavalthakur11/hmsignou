<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php'; 
/**
 * Booking Controller
 * Handles booking CRUD, check-in/out, cancellation.
 * Admin & receptionist: full access.
 * Customer: own bookings only.
 */
class BookingController extends Controller {

    private BookingModel $bookingModel;
    private RoomModel    $roomModel;
    private BillingModel $billingModel;

    public function __construct() {
        AuthMiddleware::requireAuth();
        $this->bookingModel = $this->model('Booking');
        $this->roomModel    = $this->model('Room');
        $this->billingModel = $this->model('Billing');
    }

    // ── LIST 

    /** GET /booking/index — All bookings (staff) or own bookings (customer) */
    public function index(): void {
        $filters = [];

        if (user_role() === 'customer') {
            // Customers see only their own bookings
            $filters['user_id'] = user_id();
        } else {
            // Staff filters
            $filters['status']    = $_GET['status']    ?? '';
            $filters['date_from'] = $_GET['date_from'] ?? '';
            $filters['date_to']   = $_GET['date_to']   ?? '';
            $filters['search']    = $_GET['search']    ?? '';
        }

        $data = [
            'page_title' => 'Bookings',
            'bookings'   => $this->bookingModel->getAll($filters),
            'filters'    => $filters,
            'is_staff'   => user_role() !== 'customer',
        ];

        $this->view('booking/index', $data);
    }

    /** GET /booking/mybookings — Customer's own booking history */
    public function mybookings(): void {
        AuthMiddleware::requireRole('customer');
        $data = [
            'page_title' => 'My Bookings',
            'bookings'   => $this->bookingModel->getByUser(user_id()),
            'is_staff'   => false,
        ];
        $this->view('booking/index', $data);
    }

    // ── CREATE 

    /**
     * GET  /booking/create[?room_id=X&check_in=Y&check_out=Z]
     * POST /booking/create
     */
    public function create(): void {
        $userModel = $this->model('User');

        if ($this->isPost()) {
            $errors = $this->validateBookingInput($_POST);

            if (empty($errors)) {
                $roomId   = (int) $_POST['room_id'];
                $checkIn  = $_POST['check_in'];
                $checkOut = $_POST['check_out'];

                // Confirm availability at submission time
                if (!$this->roomModel->isAvailable($roomId, $checkIn, $checkOut)) {
                    $errors[] = 'This room is no longer available for the selected dates.';
                } else {
                    // Determine which user to book for
                    $guestId = (user_role() === 'customer')
                        ? user_id()
                        : (int) ($_POST['user_id'] ?? user_id());

                    $bookingId = $this->bookingModel->create([
                        'user_id'     => $guestId,
                        'room_id'     => $roomId,
                        'check_in'    => $checkIn,
                        'check_out'   => $checkOut,
                        'guests'      => (int) $_POST['guests'],
                        'special_req' => $_POST['special_req'] ?? '',
                        'booked_by'   => user_id(),
                    ]);

                    if ($bookingId) {
                        // Auto-generate bill
                        $room    = $this->roomModel->findById($roomId);
                        $nights  = $this->bookingModel->calcNights($checkIn, $checkOut);
                        $charges = (float) $room['price_per_night'] * $nights;
                        $this->billingModel->generate($bookingId, $charges);

                        // Notify the guest
                        $this->model('Notification')->send(
                            $guestId,
                            'Booking Confirmed',
                            "Your booking #{$bookingId} has been confirmed. Check-in: {$checkIn}."
                        );

                        $this->model('Logs')->log('BOOKING_CREATE', user_id(),
                            "Booking #{$bookingId} created for user #{$guestId}.");

                        $this->flash('success', "Booking #{$bookingId} confirmed successfully!");

                        // Staff → booking list; Customer → my bookings
                        $this->redirect(
                            user_role() === 'customer'
                                ? 'booking/mybookings'
                                : 'booking/index'
                        );
                    }
                    $errors[] = 'Booking failed. Please try again.';
                }
            }

            // Re-render form with errors
            $room = $this->roomModel->findById((int)($_POST['room_id'] ?? 0));
            $this->view('booking/create', [
                'page_title'   => 'New Booking',
                'errors'       => $errors,
                'old'          => $_POST,
                'room'         => $room,
                'rooms'        => $this->roomModel->getAll(['status' => 'available']),
                'customers'    => user_role() !== 'customer'
                                    ? $userModel->getAll('customer')
                                    : [],
            ]);
            return;
        }

        // GET — pre-fill room if passed in query string
        $preRoom   = $this->roomModel->findById((int)($_GET['room_id'] ?? 0));
        $checkIn   = $_GET['check_in']  ?? date('Y-m-d');
        $checkOut  = $_GET['check_out'] ?? date('Y-m-d', strtotime('+1 day'));

        $this->view('booking/create', [
            'page_title'   => 'New Booking',
            'room'         => $preRoom,
            'rooms'        => $this->roomModel->getAll(['status' => 'available']),
            'customers'    => user_role() !== 'customer'
                                ? $userModel->getAll('customer')
                                : [],
            'old'          => [
                'room_id'   => $_GET['room_id'] ?? '',
                'check_in'  => $checkIn,
                'check_out' => $checkOut,
                'guests'    => 1,
            ],
        ]);
    }

    // ── DETAIL 

    /** GET /booking/view/{id} */
    public function show(string $id = '0'): void {
        $booking = $this->findBookingOrFail((int) $id);

        $data = [
            'page_title' => 'Booking #' . $id,
            'booking'    => $booking,
            'bill'       => $this->billingModel->findByBooking((int) $id),
        ];

        $this->view('booking/view', $data);
    }

    // ── CHECK-IN / CHECK-OUT 

    /** POST /booking/checkin/{id} */
    public function checkin(string $id = '0'): void {
        AuthMiddleware::requireStaff();
        $booking = $this->findBookingOrFail((int) $id);

        if ($booking['status'] !== 'confirmed') {
            $this->flash('error', 'Only confirmed bookings can be checked in.');
            $this->redirect('booking/index');
        }

        $this->bookingModel->checkIn((int) $id, (int) $booking['room_id']);
        $this->model('Logs')->log('CHECKIN', user_id(), "Check-in: booking #{$id}");
        $this->flash('success', "Guest checked in to Room {$booking['room_number']}.");
        $this->redirect('booking/index');
    }

    /** POST /booking/checkout/{id} */
    public function checkout(string $id = '0'): void {
        AuthMiddleware::requireStaff();
        $booking = $this->findBookingOrFail((int) $id);

        if ($booking['status'] !== 'checked_in') {
            $this->flash('error', 'Only checked-in bookings can be checked out.');
            $this->redirect('booking/index');
        }

        $this->bookingModel->checkOut((int) $id, (int) $booking['room_id']);
        $this->model('Logs')->log('CHECKOUT', user_id(), "Check-out: booking #{$id}");
        $this->flash('success', "Check-out complete for booking #{$id}.");
        $this->redirect('billing/invoice/' . $id);
    }

    // ── CANCEL 

    /** POST /booking/cancel/{id} */
    public function cancel(string $id = '0'): void {
        $booking = $this->findBookingOrFail((int) $id);

        // Customers can cancel only their own confirmed bookings
        if (user_role() === 'customer') {
            if ((int)$booking['user_id'] !== user_id()) {
                $this->flash('error', 'Permission denied.');
                $this->redirect('booking/mybookings');
            }
            if ($booking['status'] !== 'confirmed') {
                $this->flash('error', 'Only confirmed bookings can be cancelled.');
                $this->redirect('booking/mybookings');
            }
        }

        $this->bookingModel->cancel((int) $id);

        // Free the room if it was marked booked
        if ($booking['status'] === 'checked_in') {
            $this->roomModel->updateStatus((int)$booking['room_id'], 'available');
        }

        $this->model('Logs')->log('BOOKING_CANCEL', user_id(), "Booking #{$id} cancelled.");
        $this->flash('success', "Booking #{$id} has been cancelled.");
        $this->redirect(user_role() === 'customer' ? 'booking/mybookings' : 'booking/index');
    }

    // Check Availability

    /**
     * POST /booking/checkavailability
     * Returns JSON — called via fetch() from the booking form.
     */
    public function checkavailability(): void {
        header('Content-Type: application/json');

        $roomId   = (int)   ($_POST['room_id']   ?? 0);
        $checkIn  = trim($_POST['check_in']  ?? '');
        $checkOut = trim($_POST['check_out'] ?? '');

        if (!$roomId || !$checkIn || !$checkOut) {
            echo json_encode(['available' => false, 'message' => 'Missing fields.']);
            exit;
        }

        $room      = $this->roomModel->findById($roomId);
        $available = $room && $this->roomModel->isAvailable($roomId, $checkIn, $checkOut);
        $nights    = $available ? $this->bookingModel->calcNights($checkIn, $checkOut) : 0;
        $total     = $available ? round($room['price_per_night'] * $nights * 1.18, 2) : 0;

        echo json_encode([
            'available' => $available,
            'message'   => $available ? 'Room is available!' : 'Room is not available for these dates.',
            'nights'    => $nights,
            'price'     => $room['price_per_night'] ?? 0,
            'total'     => $total,
        ]);
        exit;
    }

    // ── Private helpers
    
    private function findBookingOrFail(int $id): array {
        $booking = $this->bookingModel->findById($id);
        if (!$booking) {
            $this->flash('error', 'Booking not found.');
            $this->redirect('booking/index');
        }
        // Customers must own the booking
        if (user_role() === 'customer' && (int)$booking['user_id'] !== user_id()) {
            $this->flash('error', 'Access denied.');
            $this->redirect('booking/mybookings');
        }
        return $booking;
    }

    private function validateBookingInput(array $d): array {
        $errors = [];

        $roomId   = (int)($d['room_id']   ?? 0);
        $checkIn  = trim($d['check_in']   ?? '');
        $checkOut = trim($d['check_out']  ?? '');
        $guests   = (int)($d['guests']    ?? 0);

        if (!$roomId)                     $errors[] = 'Please select a room.';
        if (!$checkIn)                    $errors[] = 'Check-in date is required.';
        if (!$checkOut)                   $errors[] = 'Check-out date is required.';
        if ($guests < 1)                  $errors[] = 'At least 1 guest is required.';

        if ($checkIn && $checkOut) {
            $ci = new DateTime($checkIn);
            $co = new DateTime($checkOut);
            $today = new DateTime('today');

            if ($ci < $today)  $errors[] = 'Check-in date cannot be in the past.';
            if ($co <= $ci)    $errors[] = 'Check-out must be after check-in.';
        }

        return $errors;
    }
}