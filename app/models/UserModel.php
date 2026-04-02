<?php
/**
 * User Model
 * Handles all user-related DB operations: auth, registration, CRUD.
 */
class UserModel extends Model {

    /**
     * Find a user by email.
     * Used during login to retrieve the hashed password.
     */
    public function findByEmail(string $email): ?array {
        $result = $this->db->query(
            "SELECT user_id, name, email, password_hash, role, is_active
               FROM users
            WHERE email = :email
                AND ROWNUM = 1",
            ['email' => $email]
        );
        return $result[0] ?? null;
    }

    /** Find a user by primary key */
    public function findById(int $id): ?array {
        $result = $this->db->query(
            "SELECT user_id, name, email, role, phone, created_at
               FROM users
              WHERE user_id = :id",
            ['id' => $id]
        );
        return $result[0] ?? null;
    }

    public function countByRole(string $role): int {
    $result = $this->db->query(
        "SELECT COUNT(*) AS TOTAL
           FROM users
          WHERE UPPER(role) = UPPER(:role)",
        ['role' => $role]
    );

    return isset($result[0]['TOTAL']) ? (int)$result[0]['TOTAL'] : 0;
    }   
    /** Get all users (admin use) */
    public function getAll(string $role = ''): array {
        $sql    = "SELECT user_id, name, email, role, phone, is_active, created_at FROM users";
        $params = [];
        if ($role !== '') {
            $sql   .= " WHERE role = :role";
            $params = ['role' => $role];
        }
        $sql .= " ORDER BY created_at DESC";
        return $this->db->query($sql, $params) ?: [];
    }

    /**
     * Register a new user.
     * Password is hashed with bcrypt before storage.
     */
    public function create(array $data): bool {
        $hash = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

        return (bool) $this->db->query(
            "INSERT INTO users (user_id, name, email, password_hash, role, phone, is_active, created_at)
             VALUES (users_seq.NEXTVAL, :name, :email, :password_hash, :role, :phone, 1, SYSDATE)",
            [
                'name'          => $this->sanitize($data['name']),
                'email'         => $this->sanitize($data['email']),
                'password_hash' => $hash,
                'role'          => $data['role'] ?? 'customer',
                'phone'         => $this->sanitize($data['phone'] ?? ''),
            ],
            false // DML — no fetch
        );
    }

    /** Update user profile */
    public function update(int $id, array $data): bool {
        return (bool) $this->db->query(
            "UPDATE users
                SET name  = :name,
                    phone = :phone
              WHERE user_id = :id",
            [
                'name'  => $this->sanitize($data['name']),
                'phone' => $this->sanitize($data['phone'] ?? ''),
                'id'    => $id,
            ],
            false
        );
    }

    /** Soft-delete: deactivate account */
    public function deactivate(int $id): bool {
        return (bool) $this->db->query(
            "UPDATE users SET is_active = 0 WHERE user_id = :id",
            ['id' => $id],
            false
        );
    }

    /** Verify password against stored hash */
    public function verifyPassword(string $plain, string $hash): bool {
        return password_verify($plain, $hash);
    }

    /** Update password */
    public function updatePassword(int $id, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
        return (bool) $this->db->query(
            "UPDATE users SET password_hash = :hash WHERE user_id = :id",
            ['hash' => $hash, 'id' => $id],
            false
        );
    }
}