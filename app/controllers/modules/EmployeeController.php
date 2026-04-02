<?php
require_once APP_PATH . '/middleware/AuthMiddleware.php';
/**
 * Employee Controller — admin only.
 */
class EmployeeController extends Controller {

    private EmployeeModel $empModel;

    public function __construct() {
        AuthMiddleware::requireAdmin();
        $this->empModel = $this->model('Employee');
    }

    /** GET /employee/index */
    public function index(): void {
        $filters = [
            'is_active'  => $_GET['is_active']  ?? '',
            'department' => $_GET['department']  ?? '',
        ];

        $this->view('employee/index', [
            'page_title'  => 'Employees',
            'employees'   => $this->empModel->getAll($filters),
            'departments' => $this->empModel->getDepartments(),
            'filters'     => $filters,
            'total_salary'=> $this->empModel->totalSalary(),
            'active_count'=> $this->empModel->countActive(),
        ]);
    }

    /** GET + POST /employee/create */
    public function create(): void {
        if ($this->isPost()) {
            $errors = $this->validate($_POST);

            if (empty($errors)) {
                $ok = $this->empModel->create($_POST);
                if ($ok) {
                    $this->model('Logs')->log('EMP_CREATE', user_id(),
                        "Employee created: {$_POST['name']}");
                    $this->flash('success', "Employee {$_POST['name']} added.");
                    $this->redirect('employee/index');
                }
                $errors[] = 'Failed to create employee. Email may already exist.';
            }

            $this->view('employee/create', [
                'page_title' => 'Add Employee',
                'errors'     => $errors,
                'old'        => $_POST,
            ]);
            return;
        }

        $this->view('employee/create', ['page_title' => 'Add Employee']);
    }

    /** GET + POST /employee/edit/{id} */
    public function edit(string $id = '0'): void {
        $emp = $this->empModel->findById((int) $id);
        if (!$emp) {
            $this->flash('error', 'Employee not found.');
            $this->redirect('employee/index');
        }

        if ($this->isPost()) {
            $errors = [];
            if (empty(trim($_POST['name'] ?? '')))        $errors[] = 'Name is required.';
            if (empty(trim($_POST['department'] ?? '')))  $errors[] = 'Department is required.';
            if (empty(trim($_POST['designation'] ?? ''))) $errors[] = 'Designation is required.';
            if ((float)($_POST['salary'] ?? 0) <= 0)      $errors[] = 'Valid salary required.';

            if (empty($errors)) {
                $ok = $this->empModel->update((int) $id, $_POST);
                if ($ok) {
                    $this->model('Logs')->log('EMP_UPDATE', user_id(),
                        "Employee #{$id} updated.");
                    $this->flash('success', 'Employee updated.');
                    $this->redirect('employee/index');
                }
                $errors[] = 'Update failed.';
            }

            $this->view('employee/edit', [
                'page_title' => 'Edit Employee',
                'emp'        => array_merge($emp, $_POST),
                'errors'     => $errors,
            ]);
            return;
        }

        $this->view('employee/edit', ['page_title' => 'Edit Employee', 'emp' => $emp]);
    }

    /** POST /employee/deactivate/{id} */
    public function deactivate(string $id = '0'): void {
        $emp = $this->empModel->findById((int) $id);
        if ($emp) {
            $this->empModel->deactivate((int) $id);
            $this->model('Logs')->log('EMP_DEACTIVATE', user_id(),
                "Employee #{$id} deactivated.");
            $this->flash('success', 'Employee deactivated.');
        }
        $this->redirect('employee/index');
    }

    private function validate(array $d): array {
        $errors = [];
        if (empty(trim($d['name']        ?? ''))) $errors[] = 'Name is required.';
        if (empty(trim($d['email']       ?? ''))) $errors[] = 'Email is required.';
        if (!filter_var($d['email'] ?? '', FILTER_VALIDATE_EMAIL))
                                                  $errors[] = 'Invalid email.';
        if (strlen($d['password'] ?? '') < 8)     $errors[] = 'Password must be 8+ characters.';
        if (empty(trim($d['department']  ?? ''))) $errors[] = 'Department is required.';
        if (empty(trim($d['designation'] ?? ''))) $errors[] = 'Designation is required.';
        if ((float)($d['salary'] ?? 0) <= 0)      $errors[] = 'Valid salary required.';
        return $errors;
    }
}