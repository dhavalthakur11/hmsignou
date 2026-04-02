<?php
/**
 * Employee Model
 * Manages employee records joined with their user accounts.
 */
class EmployeeModel extends Model {

    /** Get all employees with linked user data */
    public function getAll(array $filters = []): array {
        $sql = "SELECT e.employee_id, e.department, e.designation,
                       e.salary, e.hire_date, e.is_active,
                       u.user_id, u.name, u.email, u.phone, u.role
                  FROM employees e
                  JOIN users u ON e.user_id = u.user_id
                 WHERE 1=1";
        $params = [];

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND e.is_active = :is_active";
            $params['is_active'] = (int) $filters['is_active'];
        }
        if (!empty($filters['department'])) {
            $sql .= " AND LOWER(e.department) = LOWER(:dept)";
            $params['dept'] = $filters['department'];
        }

        $sql .= " ORDER BY e.hire_date DESC";
        return $this->db->query($sql, $params) ?: [];
    }

    /** Find one employee by employee_id */
    public function findById(int $id): ?array {
        $result = $this->db->query(
            "SELECT e.*, u.name, u.email, u.phone, u.role
               FROM employees e
               JOIN users u ON e.user_id = u.user_id
              WHERE e.employee_id = :id",
            ['id' => $id]
        );
        return $result[0] ?? null;
    }

    /** Create employee + linked user account in one transaction */
    public function create(array $d): bool {
        // 1. Insert the user account
        $hash = password_hash($d['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        $userOk = $this->db->query(
            "INSERT INTO users
                (user_id, name, email, password_hash, role, phone, is_active)
             VALUES
                (users_seq.NEXTVAL, :name, :email, :hash, :role, :phone, 1)",
            [
                'name'  => $this->sanitize($d['name']),
                'email' => $this->sanitize($d['email']),
                'hash'  => $hash,
                'role'  => $d['role'] ?? 'receptionist',
                'phone' => $this->sanitize($d['phone'] ?? ''),
            ],
            false
        );

        if (!$userOk) return false;

        // 2. Get the new user_id via sequence CURRVAL
        $userId = $this->db->lastInsertId('users_seq');

        // 3. Insert employee record
        return (bool) $this->db->query(
            "INSERT INTO employees
                (employee_id, user_id, department, designation,
                 salary, hire_date, is_active)
             VALUES
                (employees_seq.NEXTVAL, :user_id, :department, :designation,
                 :salary, TO_DATE(:hire_date,'YYYY-MM-DD'), 1)",
            [
                'user_id'     => $userId,
                'department'  => $this->sanitize($d['department']),
                'designation' => $this->sanitize($d['designation']),
                'salary'      => (float) $d['salary'],
                'hire_date'   => $d['hire_date'] ?? date('Y-m-d'),
            ],
            false
        );
    }

    /** Update employee + user details */
    public function update(int $empId, array $d): bool {
        // Update user info
        $this->db->query(
            "UPDATE users
                SET name  = :name,
                    phone = :phone,
                    role  = :role
              WHERE user_id = (
                  SELECT user_id FROM employees WHERE employee_id = :emp_id
              )",
            [
                'name'   => $this->sanitize($d['name']),
                'phone'  => $this->sanitize($d['phone'] ?? ''),
                'role'   => $d['role'] ?? 'receptionist',
                'emp_id' => $empId,
            ],
            false
        );

        // Update employee info
        return (bool) $this->db->query(
            "UPDATE employees
                SET department  = :department,
                    designation = :designation,
                    salary      = :salary,
                    hire_date   = TO_DATE(:hire_date,'YYYY-MM-DD'),
                    is_active   = :is_active
              WHERE employee_id = :id",
            [
                'department'  => $this->sanitize($d['department']),
                'designation' => $this->sanitize($d['designation']),
                'salary'      => (float) $d['salary'],
                'hire_date'   => $d['hire_date'],
                'is_active'   => (int) $d['is_active'],
                'id'          => $empId,
            ],
            false
        );
    }

    /** Soft-deactivate an employee */
    public function deactivate(int $id): bool {
        $this->db->query(
            "UPDATE users SET is_active = 0
              WHERE user_id = (
                  SELECT user_id FROM employees WHERE employee_id = :id
              )",
            ['id' => $id],
            false
        );
        return (bool) $this->db->query(
            "UPDATE employees SET is_active = 0 WHERE employee_id = :id",
            ['id' => $id],
            false
        );
    }

    /** Get distinct departments */
    public function getDepartments(): array {
        $rows = $this->db->query(
            "SELECT DISTINCT department FROM employees
              WHERE department IS NOT NULL ORDER BY department"
        );
        return array_column($rows ?: [], 'department');
    }

    /** Total salary expenditure */
    public function totalSalary(): float {
        $r = $this->db->query(
            "SELECT NVL(SUM(salary), 0) AS total
               FROM employees WHERE is_active = 1"
        );
        return (float)($r[0]['total'] ?? 0);
    }

    /** Count active employees */
    public function countActive(): int {
        $r = $this->db->query(
            "SELECT COUNT(*) AS cnt FROM employees WHERE is_active = 1"
        );
        return (int)($r[0]['cnt'] ?? 0);
    }
}