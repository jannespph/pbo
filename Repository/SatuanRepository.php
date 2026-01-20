<?php

final class SatuanRepository
{
    public function __construct(private PDO $pdo) {}

   
    public function findAll(): array
    {
        $sql = "SELECT id_satuan, nama_satuan
                FROM tbl_satuan
                ORDER BY id_satuan DESC";
        return $this->pdo->query($sql)->fetchAll();
    }

    public function insert(string $namaSatuan): int
    {
        $sql = "INSERT INTO tbl_satuan (nama_satuan) VALUES (:nama)";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':nama' => $namaSatuan]);
        return (int)$this->pdo->lastInsertId();
    }

    public function deleteById(int $id): void
    {
        $sql = "DELETE FROM tbl_satuan WHERE id_satuan = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}

?>
