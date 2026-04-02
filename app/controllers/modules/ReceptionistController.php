<?php
/**
 * Receptionist Controller
 * Front-desk dashboard: today's arrivals, departures, and live room grid.
 */
class ReceptionistController extends Controller {

    public function __construct() {
        AuthMiddleware::requireStaff();
    }

    /** GET /receptionist/dashboard */
    public function dashboard(): void {
        $bookingModel = $this->model('Booking');
        $roomModel    = $this->model('Room');

        $data = [
            'page_title'        => 'Front Desk',
            'arrivals'          => $bookingModel->getTodayArrivals(),
            'departures'        => $bookingModel->getTodayDepartures(),
            'available_rooms'   => $roomModel->getAll(['status' => 'available']),
            'booked_rooms'      => $roomModel->getAll(['status' => 'booked']),
            'maintenance_rooms' => $roomModel->getAll(['status' => 'maintenance']),
            'stats' => [
                'arrivals_today'    => count($bookingModel->getTodayArrivals()),
                'departures_today'  => count($bookingModel->getTodayDepartures()),
                'available'         => $roomModel->countByStatus('available'),
                'booked'            => $roomModel->countByStatus('booked'),
            ],
        ];

        $this->view('receptionist/dashboard', $data);
    }
}