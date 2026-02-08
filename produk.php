<?php
declare(strict_types=1);

require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';

require __DIR__ . '/Repository/CategoryRepository.php';
require __DIR__ . '/Repository/SatuanRepository.php';
require __DIR__ . '/Repository/ProductRepository.php';

$pdo = Database::pdo();
$categoryRepo = new CategoryRepository($pdo);
$satuanRepo   = new SatuanRepository($pdo);
$productRepo  = new ProductRepository($pdo);

$errors = [];
$success = '';

/* TAMBAH PRODUK */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sku        = trim($_POST['sku'] ?? '');
    $nama       = trim($_POST['nama_produk'] ?? '');
    $kategoriId = (int)($_POST['id_kategori'] ?? 0);
    $satuanId   = (int)($_POST['id_satuan'] ?? 0);
    $harga      = (float)($_POST['harga'] ?? 0);

    // Validasi sesuai struktur kolom Anda
    if ($sku === '') $errors[] = 'SKU wajib diisi.';
    if ($nama === '') $errors[] = 'Nama produk wajib diisi.';
    if ($kategoriId <= 0) $errors[] = 'Kategori wajib dipilih.';
    if ($satuanId <= 0) $errors[] = 'Satuan wajib dipilih.';
    if ($harga < 0) $errors[] = 'Harga tidak boleh negatif.';

    if (mb_strlen($sku) > 25) $errors[] = 'SKU maksimal 25 karakter.';
    if (mb_strlen($nama) > 50) $errors[] = 'Nama produk maksimal 50 karakter.';

    if (!$errors) {
        try {
            $productRepo->insert($sku, $nama, $kategoriId, $satuanId, $harga);
            header('Location: produk.php?ok=1'); // PRG: anti double insert saat refresh
            exit;
        } catch (PDOException $e) {
            // 23000 biasanya unique constraint (SKU dobel)
            $errors[] = ($e->getCode() === '23000') 
                ? 'SKU sudah ada (duplikat).' 
                : 'Error DB: ' . $e->getMessage();
        }
    }
}

/* HAPUS PRODUK */
if (($_GET['action'] ?? '') === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $productRepo->deleteById($id);
    }
    header('Location: produk.php');
    exit;
}

if (($_GET['ok'] ?? '') === '1') {
    $success = 'Produk berhasil ditambahkan.';
}

$categories = $categoryRepo->findAll();
$satuans    = $satuanRepo->findAll();
$products   = $productRepo->findAllWithMaster();

?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>POS - Produk</title>
</head>
<body>
    <h1>Master Produk</h1>
    <p><a href="index.php">Kembali</a></p>

    <?php if ($success): ?>
        <p style="color:green;"><?= htmlspecialchars($success) ?></p>
    <?php endif; ?>

    <?php if ($errors): ?>
        <ul style="color:red;">
            <?php foreach ($errors as $err): ?>
                <li><?= htmlspecialchars($err) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>

    <h3>Tambah Produk</h3>
    <form method="post">
        <div>
            <label>SKU:</label>
            <input name="sku" maxlength="25" required>
        </div>

        <div>
            <label>Nama Produk:</label>
            <input name="nama_produk" maxlength="50" required>
        </div>

        <div>
            <label>Kategori:</label>
            <select name="id_kategori" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($categories as $c): ?>
                    <option value="<?= (int)$c['id_kategori'] ?>">
                        <?= htmlspecialchars($c['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Satuan:</label>
            <select name="id_satuan" required>
                <option value="">-- Pilih --</option>
                <?php foreach ($satuans as $s): ?>
                    <option value="<?= (int)$s['id_satuan'] ?>">
                        <?= htmlspecialchars($s['nama_satuan']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label>Harga:</label>
            <input name="harga" type="number" step="0.01" value="0.00" required>
        </div>

        <button type="submit">Simpan</button>
    </form>

    <hr>

    <h3>Daftar Produk</h3>
    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>SKU</th>
                <th>Nama</th>
                <th>Kategori</th>
                <th>Satuan</th>
                <th>Harga</th>
                <th>Aktif</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($products as $p): ?>
                <tr>
                    <td><?= (int)$p['id_produk'] ?></td>
                    <td><?= htmlspecialchars($p['sku']) ?></td>
                    <td><?= htmlspecialchars($p['nama_produk']) ?></td>
                    <td><?= htmlspecialchars($p['nama_kategori']) ?></td>
                    <td><?= htmlspecialchars($p['nama_satuan']) ?></td>
                    <td><?= htmlspecialchars((string)$p['harga']) ?></td>
                    <td><?= ((int)$p['is_active'] === 1) ? 'Ya' : 'Tidak' ?></td>
                    <td>
                        <a href="produk.php?action=delete&id=<?= (int)$p['id_produk'] ?>" 
                           onclick="return confirm('Hapus produk ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>

            <?php if (!$products): ?>
                <tr><td colspan="8">Belum ada data produk.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>