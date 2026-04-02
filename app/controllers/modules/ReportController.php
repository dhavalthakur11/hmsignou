<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Report Controller — admin only.
 */
class ReportController extends Controller {

    public function __construct() {
        AuthMiddleware::requireAdmin();
    }

    public function index(): void {
        $reportModel = $this->model('Report');

        $this->view('report/index', [
            'page_title'    => 'Reports',
            'occupancy'     => $reportModel->occupancySummary(),
            'revenue'       => $reportModel->revenueSummary(),
            'trends'        => $reportModel->bookingTrends(),
            'monthly'       => $reportModel->monthlyRevenue(),
            'top_rooms'     => $reportModel->topRooms(),
            'by_status'     => $reportModel->bookingsByStatus(),
        ]);
    }
}