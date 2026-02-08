<?php

final class ProductRepository
{
    public function __construct(private PDO $pdo) {}

    public function findAllWithMaster(): array
    {
        $sql = "
            SELECT
                p.id_produk,
                p.sku,
                p.nama_produk,
                p.harga,
                p.is_active,
                p.created_at,
                p.updated_at,
                k.nama_kategori,
                s.nama_satuan
            FROM tbl_produk p
            JOIN tbl_kategori k ON k.id_kategori = p.id_kategori
            JOIN tbl_satuan s ON s.id_satuan = p.id_satuan
            ORDER BY p.id_produk DESC
        ";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function insert(string $sku, string $nama, int $kategoriId, int $satuanId, float $harga): int
    {
        $sql = "
            INSERT INTO tbl_produk (sku, nama_produk, id_kategori, id_satuan, harga, is_active)
            VALUES (:sku, :nama, :kategori, :satuan, :harga, 1)
        ";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':sku'      => $sku,
            ':nama'     => $nama,
            ':kategori' => $kategoriId,
            ':satuan'   => $satuanId,
            ':harga'    => $harga,
        ]);

        return (int)$this->pdo->lastInsertId();
    }

    public function deleteById(int $id): void
    {
        $sql = "DELETE FROM tbl_produk WHERE id_produk = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
    }
}

?>