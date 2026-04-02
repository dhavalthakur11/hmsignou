<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Billing Controller
 * Invoice display, payment processing, extras management.
 */
class BillingController extends Controller {

    private BillingModel  $billingModel;
    private BookingModel  $bookingModel;

    public function __construct() {
        AuthMiddleware::requireAuth();
        $this->billingModel = $this->model('Billing');
        $this->bookingModel = $this->model('Booking');
    }

    // ── Billing index (staff only) ────────────────────────────────────────────

    /** GET /billing/index */
    public function index(): void {
        AuthMiddleware::requireStaff();

        $filters = [
            'payment_status' => $_GET['payment_status'] ?? '',
        ];

        $this->view('billing/index', [
            'page_title' => 'Billing',
            'bills'      => $this->billingModel->getAll($filters),
            'filters'    => $filters,
        ]);
    }

    // ── Invoice ───────────────────────────────────────────────────────────────

    /**
     * GET /billing/invoice/{booking_id}
     * Accessible by: the guest who owns the booking, or any staff member.
     */
    public function invoice(string $bookingId = '0'): void {
        $booking = $this->bookingModel->findById((int) $bookingId);

        if (!$booking) {
            $this->flash('error', 'Booking not found.');
            $this->redirect('booking/index');
        }

        // Customers can only view their own invoices
        if (user_role() === 'customer' && (int)$booking['user_id'] !== user_id()) {
            $this->flash('error', 'Access denied.');
            $this->redirect('booking/mybookings');
        }

        $bill = $this->billingModel->findByBooking((int) $bookingId);

        // Auto-generate bill if it was somehow missed
        if (!$bill) {
            $nights  = $this->bookingModel->calcNights(
                $booking['check_in'], $booking['check_out']
            );
            $charges = (float)$booking['price_per_night'] * $nights;
            $this->billingModel->generate((int)$bookingId, $charges);
            $bill = $this->billingModel->findByBooking((int) $bookingId);
        }

        $this->view('billing/invoice', [
            'page_title' => 'Invoice #' . $bookingId,
            'booking'    => $booking,
            'bill'       => $bill,
            'nights'     => $this->bookingModel->calcNights(
                                $booking['check_in'], $booking['check_out']
                            ),
        ]);
    }

    // ── Mark as Paid ─────────────────────────────────────────────────────────

    /** POST /billing/pay/{bill_id} */
    public function pay(string $billId = '0'): void {
        AuthMiddleware::requireStaff();

        $method = trim(filter_input(INPUT_POST, 'payment_method', FILTER_DEFAULT) ?? 'cash');
        $allowed = ['cash', 'card', 'upi', 'bank_transfer', 'online'];

        if (!in_array($method, $allowed, true)) {
            $this->flash('error', 'Invalid payment method.');
            $this->redirect('billing/index');
        }

        $bill = $this->billingModel->findById((int) $billId);
        if (!$bill) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('billing/index');
        }

        $ok = $this->billingModel->markPaid((int) $billId, $method);

        if ($ok) {
            $this->model('Logs')->log('PAYMENT', user_id(),
                "Bill #{$billId} marked paid via {$method}. "
                . "Amount: ₹{$bill['total_amount']}");

            // Notify the guest
            $booking = $this->bookingModel->findById((int)$bill['booking_id']);
            if ($booking) {
                $this->model('Notification')->send(
                    (int)$booking['user_id'],
                    'Payment Received',
                    "Payment of ₹{$bill['total_amount']} received for booking "
                    . "#{$bill['booking_id']}. Thank you!"
                );
            }

            $this->flash('success', "Payment of ₹{$bill['total_amount']} recorded.");
        } else {
            $this->flash('error', 'Failed to record payment.');
        }

        $this->redirect('billing/invoice/' . $bill['booking_id']);
    }

    // ── Update Extra Charges ──────────────────────────────────────────────────

    /** POST /billing/extras/{bill_id} */
    public function extras(string $billId = '0'): void {
        AuthMiddleware::requireStaff();

        $extraCharges = (float) filter_input(INPUT_POST, 'extra_charges', FILTER_VALIDATE_FLOAT);
        $notes        = trim(filter_input(INPUT_POST, 'notes', FILTER_DEFAULT) ?? '');

        if ($extraCharges < 0) {
            $this->flash('error', 'Extra charges cannot be negative.');
            $this->redirect('billing/index');
        }

        $bill = $this->billingModel->findById((int) $billId);
        if (!$bill) {
            $this->flash('error', 'Bill not found.');
            $this->redirect('billing/index');
        }

        $ok = $this->billingModel->updateExtras((int) $billId, $extraCharges, $notes);

        if ($ok) {
            $this->model('Logs')->log('EXTRAS_UPDATED', user_id(),
                "Bill #{$billId} extra charges updated to ₹{$extraCharges}.");
            $this->flash('success', 'Extra charges updated successfully.');
        } else {
            $this->flash('error', 'Failed to update extra charges.');
        }

        $this->redirect('billing/invoice/' . $bill['booking_id']);
    }
}