<?php

require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Repository/CategoryRepository.php';

$repo = new CategoryRepository(Database::pdo());
$errors = [];
$success = '';

/* Baru */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_kategori'] ?? '');

    if ($nama === '') {
        $errors[] = 'Nama kategori wajib diisi.';
    } elseif (mb_strlen($nama) > 50) {
        $errors[] = 'Nama kategori maksimal 50 karakter.';
    }

    if (!$errors) {
        try {
            $repo->insert($nama);
            header('Location: kategori.php?ok=1'); // PRG: biar refresh tidak double insert
            exit;
        } catch (PDOException $e) {
            // Duplicate key biasanya SQLSTATE 23000 (kalau nama_kategori dibuat UNIQUE)
            $errors[] = ($e->getCode() === '23000')
                ? 'Kategori sudah ada (duplikat).'
                : 'Error DB: ' . $e->getMessage();
        }
    }
}

/* DELETE */
if (($_GET['action'] ?? '') === 'delete') {
    $id = (int)($_GET['id'] ?? 0);
    if ($id > 0) {
        $repo->deleteById($id);
    }
    header('Location: kategori.php');
    exit;
}

if (($_GET['ok'] ?? '') === '1') {
    $success = 'Kategori berhasil ditambahkan.';
}

$rows = $repo->findAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Master Kategori</title></head>
<body>
    <h1>Master Kategori</h1>
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

    <form method="post">
        <label>Nama Kategori:</label>
        <input name="nama_kategori" maxlength="50" required>
        <button type="submit">Tambah</button>
    </form>

    <hr>

    <table border="1" cellpadding="6" cellspacing="0">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($rows as $r): ?>
                <tr>
                    <td><?= (int)$r['id_kategori'] ?></td>
                    <td><?= htmlspecialchars($r['nama_kategori']) ?></td>
                    <td>
                        <a href="kategori.php?action=delete&id=<?= (int)$r['id_kategori'] ?>" 
                           onclick="return confirm('Hapus kategori ini?')">Hapus</a>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (!$rows): ?>
                <tr><td colspan="3">Belum ada data.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>