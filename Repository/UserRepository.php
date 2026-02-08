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

    // Tambah user (PASSWORD DI-HASH / ANEH)
    public function insert(string $username, string $plainPassword, string $role): int
    {
        $role = strtoupper(trim($role));
        if (!in_array($role, ['ADMIN', 'USER'], true)) {
            throw new InvalidArgumentException('Role tidak valid');
        }

        // 🔐 HASH PASSWORD
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

    // Hapus user
    public function deleteById(int $id): void
    {
        $sql = "DELETE FROM users WHERE id_user = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }

    // LOGIN (PAKAI password_verify)
    public function verifyLogin(string $username, string $plainPassword): ?array
    {
        $user = $this->findByUsername($username);
        if (!$user) return null;

        // ✅ COCOKKAN HASH
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
