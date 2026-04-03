<?php

require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Room Controller
 * CRUD for rooms. Admin can do everything; receptionists can view only.
 */
class RoomController extends Controller {

    private RoomModel $roomModel;

    public function __construct() {
        AuthMiddleware::requireStaff();
        $this->roomModel = $this->model('Room');
    }

    /** GET /room/index — Room listing with filters */
    public function index(): void {
        $filters = [
            'status'    => $_GET['status']    ?? '',
            'room_type' => $_GET['room_type'] ?? '',
            'floor'     => $_GET['floor']     ?? '',
        ];

        $data = [
            'page_title' => 'Room Management',
            'rooms'      => $this->roomModel->getAll($filters),
            'room_types' => $this->roomModel->getRoomTypes(),
            'filters'    => $filters,
            'stats'      => [
                'total'       => $this->roomModel->count(),
                'available'   => $this->roomModel->countByStatus('available'),
                'booked'      => $this->roomModel->countByStatus('booked'),
                'maintenance' => $this->roomModel->countByStatus('maintenance'),
            ],
        ];

        $this->view('room/index', $data);
    }

    /** GET /room/create  |  POST /room/create — Add room form + handler */
    public function create(): void {
        AuthMiddleware::requireAdmin();

        if ($this->isPost()) {
            $errors = $this->validateRoomInput($_POST);

            if (empty($errors)) {
                if ($this->roomModel->numberExists($_POST['room_number'])) {
                    $errors[] = "Room number '{$_POST['room_number']}' already exists.";
                } else {
                    $ok = $this->roomModel->create($_POST);
                    if ($ok) {
                        $this->model('Logs')->log('ROOM_CREATE', user_id(),
                            "Room #{$_POST['room_number']} created.");
                        $this->flash('success', "Room {$_POST['room_number']} added successfully.");
                        $this->redirect('room/index');
                    }
                    $errors[] = 'Failed to create room. Please try again.';
                }
            }

            $this->view('room/create', [
                'page_title' => 'Add Room',
                'errors'     => $errors,
                'old'        => $_POST,
            ]);
            return;
        }

        $this->view('room/create', ['page_title' => 'Add Room']);
    }

    /** GET /room/edit/{id}  |  POST /room/edit/{id} — Edit room */
    public function edit(string $id = '0'): void {
        AuthMiddleware::requireAdmin();

        $room = $this->roomModel->findById((int) $id);
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('room/index');
        }

        if ($this->isPost()) {
            $errors = $this->validateRoomInput($_POST);

            if (empty($errors)) {
                if ($this->roomModel->numberExists($_POST['room_number'], (int) $id)) {
                    $errors[] = "Room number '{$_POST['room_number']}' already in use.";
                } else {
                    $ok = $this->roomModel->update((int) $id, $_POST);
                    if ($ok) {
                        $this->model('Logs')->log('ROOM_UPDATE', user_id(),
                            "Room #{$_POST['room_number']} updated.");
                        $this->flash('success', 'Room updated successfully.');
                        $this->redirect('room/index');
                    }
                    $errors[] = 'Update failed. Please try again.';
                }
            }

            $this->view('room/edit', [
                'page_title' => 'Edit Room',
                'room'       => array_merge($room, $_POST),
                'errors'     => $errors,
            ]);
            return;
        }

        $this->view('room/edit', ['page_title' => 'Edit Room', 'room' => $room]);
    }

    /** POST /room/delete/{id} — Delete a room */
    public function delete(string $id = '0'): void {
        AuthMiddleware::requireAdmin();

        if (!$this->isPost()) {
            $this->redirect('room/index');
        }

        $room = $this->roomModel->findById((int) $id);
        if (!$room) {
            $this->flash('error', 'Room not found.');
            $this->redirect('room/index');
        }

        if ($this->roomModel->hasBookings((int) $id)) {
            $this->flash('error', 'Cannot delete — this room has booking history.');
            $this->redirect('room/index');
        }

        $this->roomModel->delete((int) $id);
        $this->model('Logs')->log('ROOM_DELETE', user_id(),
            "Room #{$room['room_number']} deleted.");
        $this->flash('success', "Room {$room['room_number']} deleted.");
        $this->redirect('room/index');
    }

    /** POST /room/status/{id} — Quick status toggle */
    public function status(string $id = '0'): void {
        AuthMiddleware::requireStaff();

        $newStatus = $_POST['status'] ?? '';
        $allowed   = ['available', 'booked', 'maintenance', 'checkout'];

        if (!in_array($newStatus, $allowed, true)) {
            $this->flash('error', 'Invalid status.');
            $this->redirect('room/index');
        }

        $this->roomModel->updateStatus((int) $id, $newStatus);
        $this->model('Logs')->log('ROOM_STATUS', user_id(),
            "Room #{$id} status → {$newStatus}");
        $this->flash('success', 'Room status updated.');
        $this->redirect('room/index');
    }

    // ── Private helpers 

    private function validateRoomInput(array $d): array {
        $errors = [];
        if (empty(trim($d['room_number'] ?? '')))       $errors[] = 'Room number is required.';
        if (empty(trim($d['room_type'] ?? '')))         $errors[] = 'Room type is required.';
        if (!isset($d['price_per_night']) || (float)$d['price_per_night'] <= 0)
                                                        $errors[] = 'Price must be greater than 0.';
        if (!isset($d['capacity']) || (int)$d['capacity'] < 1)
                                                        $errors[] = 'Capacity must be at least 1.';
        if (!isset($d['floor']) || (int)$d['floor'] < 1)
                                                        $errors[] = 'Floor must be at least 1.';
        return $errors;
    }
}