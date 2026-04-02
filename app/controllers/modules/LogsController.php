<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Logs Controller — admin only.
 */
class LogsController extends Controller {

    public function __construct() {
        AuthMiddleware::requireAdmin();
    }

    public function index(): void {
        $filters = [
            'action'    => $_GET['action']    ?? '',
            'date_from' => $_GET['date_from'] ?? '',
            'date_to'   => $_GET['date_to']   ?? '',
        ];

        $logsModel = $this->model('Logs');

        $this->view('logs/index', [
            'page_title' => 'Audit Logs',
            'logs'       => $logsModel->getFiltered($filters),
            'filters'    => $filters,
            'actions'    => $logsModel->getDistinctActions(),
        ]);
    }
    
}