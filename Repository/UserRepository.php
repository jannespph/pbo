<?php
declare(strict_types=1);

final class UserRepository
{
    public function __construct(private PDO $pdo) {}

    // Ambil semua user
    public function findAll(): array
    {
        $sql = "SELECT id_user, username, role, created_at
                FROM users
                ORDER BY id_user DESC";
        return $this->pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // Cari user berdasarkan username
    public function findByUsername(string $username): ?array
    {
        $sql = "SELECT id_user, username, password, role
                FROM users
                WHERE username = :username
                LIMIT 1";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':username' => $username]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // Tambah user (password di-hash)
    public function insert(string $username, string $plainPassword, string $role): int
    {
        $role = strtoupper(trim($role));

        // Terima role ADMIN atau CASHIER
        if (!in_array($role, ['ADMIN', 'CASHIER'], true)) {
            throw new InvalidArgumentException('Role tidak valid');
        }

        $hashPassword = password_hash($plainPassword, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (username, password, role)
                VALUES (:username, :password, :role)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashPassword,
            ':role'     => $role,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    // Hapus user berdasarkan ID
    public function deleteById(int $id): void
    {
        $sql = "DELETE FROM users WHERE id_user = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    // Update password user (opsional)
    public function updatePassword(int $id, string $newPassword): void
    {
        $hash = password_hash($newPassword, PASSWORD_DEFAULT);
        $sql = "UPDATE users SET password = :password WHERE id_user = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':password' => $hash, ':id' => $id]);
    }

    // Update role user (opsional)
    public function updateRole(int $id, string $role): void
    {
        $role = strtoupper(trim($role));
        if (!in_array($role, ['ADMIN', 'CASHIER'], true)) {
            throw new InvalidArgumentException('Role tidak valid');
        }
        $sql = "UPDATE users SET role = :role WHERE id_user = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':role' => $role, ':id' => $id]);
    }

    // Login / verifikasi username & password
    public function verifyLogin(string $username, string $plainPassword): ?array
    {
        $user = $this->findByUsername($username);
        if (!$user) return null;

        
        if (!password_verify($plainPassword, $user['password'])) {
            return null;
        }

        return [
            'id_user'  => (int)$user['id_user'],
            'username' => $user['username'],
            'role'     => $user['role'],
        ];
    }
}
