<?php

require __DIR__ . '/config.php';
require __DIR__ . '/Database.php';
require __DIR__ . '/Repository/SatuanRepository.php';

$repo = new SatuanRepository(Database::pdo());
$errors = [];
$success = '';

/* Baru */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama = trim($_POST['nama_satuan'] ?? '');

    if ($nama === '') {
        $errors[] = 'Nama satuan wajib diisi.';
    } elseif (mb_strlen($nama) > 50) {
        $errors[] = 'Nama satuan maksimal 50 karakter.';
    }

    if (!$errors) {
        try {
            $repo->insert($nama);
            header('Location: satuan.php?ok=1'); // PRG: biar refresh tidak double insert
            exit;
        } catch (PDOException $e) {
            // Duplicate key biasanya SQLSTATE 23000 (kalau nama_satuan dibuat UNIQUE)
            $errors[] = ($e->getCode() === '23000')
                ? 'Satuan sudah ada (duplikat).'
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
    header('Location: satuan.php');
    exit;
}

if (($_GET['ok'] ?? '') === '1') {
    $success = 'Satuan berhasil ditambahkan.';
}

$rows = $repo->findAll();
?>
<!doctype html>
<html>
<head><meta charset="utf-8"><title>Master Satuan</title></head>
<body>
    <h1>Master Satuan</h1>
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
        <label>Nama Satuan:</label>
        <input name="nama_satuan" maxlength="50" required>
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
                    <td><?= (int)$r['id_satuan'] ?></td>
                    <td><?= htmlspecialchars($r['nama_satuan']) ?></td>
                    <td>
                        <a href="satuan.php?action=delete&id=<?= (int)$r['id_satuan'] ?>" 
                           onclick="return confirm('Hapus satuan ini?')">Hapus</a>
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