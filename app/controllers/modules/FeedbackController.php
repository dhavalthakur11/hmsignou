<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';

/**
 * Feedback Controller
 * Customers submit reviews; admin views all feedback.
 */
class FeedbackController extends Controller {

    public function __construct() {
        AuthMiddleware::requireAuth();
    }

    /** GET /feedback/index — Admin view all feedback */
    public function index(): void {
        AuthMiddleware::requireAdmin();

        $fbModel = $this->model('Feedback');

        $this->view('feedback/index', [
            'page_title' => 'Customer Feedback',
            'feedbacks'  => $fbModel->getAll(),
            'avg_rating' => $fbModel->getAverageRating(),
        ]);
    }

    /** GET + POST /feedback/form — Customer submits feedback */
    public function form(): void {
        AuthMiddleware::requireRole('customer');

        $bookingModel = $this->model('Booking');

        // Customer's completed stays only
        $myStays = array_filter(
            $bookingModel->getByUser(user_id()),
            fn($b) => ($b['status'] ?? '') === 'checked_out'
        );

        if ($this->isPost()) {
            $rating    = (int)($_POST['rating'] ?? 0);
            $comment   = trim((string)($_POST['comment'] ?? ''));
            $bookingId = (int)($_POST['booking_id'] ?? 0);

            $errors = [];

            if ($rating < 1 || $rating > 5) {
                $errors[] = 'Please select a rating (1–5).';
            }

            if (strlen($comment) < 5) {
                $errors[] = 'Please write at least a short comment.';
            }

            if (!$bookingId) {
                $errors[] = 'Please select a stay.';
            }

            if (empty($errors)) {
                $ok = $this->model('Feedback')->create([
                    'user_id'    => user_id(),
                    'booking_id' => $bookingId,
                    'rating'     => $rating,
                    'comment'    => $comment,
                ]);

                if ($ok) {
                    $this->model('Logs')->log(
                        'FEEDBACK',
                        user_id(),
                        "Feedback submitted for booking #{$bookingId}."
                    );

                    $this->flash('success', 'Thank you for your feedback!');
                    $this->redirect('customer/dashboard');
                    return;
                }

                $errors[] = 'Could not save feedback. Please try again.';
            }

            $this->view('feedback/form', [
                'page_title' => 'Leave Feedback',
                'errors'     => $errors,
                'my_stays'   => $myStays,
                'old'        => [
                    'booking_id' => $bookingId,
                    'rating'     => $rating,
                    'comment'    => $comment,
                ],
            ]);
            return;
        }

        $this->view('feedback/form', [
            'page_title' => 'Leave Feedback',
            'my_stays'   => $myStays,
        ]);
    }
}