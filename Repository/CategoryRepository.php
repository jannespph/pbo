<?php

final class CategoryRepository
{
    public function __construct(private PDO $pdo) {}

   
    public function findAll(): array
    {
        $sql = "SELECT id_kategori, nama_kategori
                FROM tbl_kategori
                ORDER BY id_kategori DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function insert(string $namaKategori): int
    {
        $sql = "INSERT INTO tbl_kategori (nama_kategori) VALUES (:nama)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nama' => $namaKategori]);
        return (int)$this->pdo->lastInsertId();
    }

    public function deleteById(int $id): void
    {
        $sql = "DELETE FROM tbl_kategori WHERE id_kategori = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}

?>
