<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Notification Controller
 */
class NotificationController extends Controller {

    public function __construct() {
        AuthMiddleware::requireAuth();
    }

    public function index(): void {
        $notifModel = $this->model('Notification');
        $notifModel->markAllRead(user_id());

        $this->view('notification/index', [
            'page_title'    => 'Notifications',
            'notifications' => $notifModel->getForUser(user_id()),
        ]);
    }
}