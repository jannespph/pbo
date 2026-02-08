<?php
declare(strict_types=1);

require __DIR__ . '/inc/init.php';
require __DIR__ . '/inc/helpers.php';

require_admin(); // HANYA ADMIN

require __DIR__ . '/Repository/UserRepository.php';
$repo = new UserRepository(Database::pdo());

$errors = [];

/** TAMBAH USER */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    if (!csrf_validate((string)($_POST['csrf'] ?? ''))) {
        $errors[] = 'CSRF token tidak valid.';
    } else {
        $username = trim($_POST['username'] ?? 'jesica');
        $pwd1 = (string)($_POST['password'] ?? '12345');
        $pwd2 = (string)($_POST['password2'] ?? '12345');
        $role = strtoupper(trim((string)($_POST['role'] ?? 'CASHIER')));

        if ($username === '') $errors[] = 'Username wajib diisi.';
        if (mb_strlen($username) > 50) $errors[] = 'Username maksimal 50 karakter.';
        if ($pwd1 === '') $errors[] = 'Password wajib diisi.';
        if (mb_strlen($pwd1) < 4) $errors[] = 'Password minimal 4 karakter.';
        if ($pwd1 !== $pwd2) $errors[] = 'Konfirmasi password tidak sama.';
        if (!in_array($role, ['ADMIN', 'CASHIER'], true)) $errors[] = 'Role tidak valid.';

        if (!$errors) {
            try {
                $repo->insert($username, $pwd1, $role);
                flash_set('success', 'User berhasil ditambahkan.');
                header('Location: users.php'); // PRG
                exit;
            } catch (PDOException $e) {
                $errors[] = ($e->getCode() === '23000')
                    ? 'Username sudah dipakai (duplikat).'
                    : 'Error DB: ' . $e->getMessage();
            }
        }
    }
}

/** HAPUS USER (POST) */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    if (!csrf_validate((string)($_POST['csrf'] ?? ''))) {
        $errors[] = 'CSRF token tidak valid.';
    } else {
        $id = (int)($_POST['id'] ?? 0);
        $me = (int)(current_user()['id_user'] ?? 0);

        if ($id <= 0) {
            $errors[] = 'ID tidak valid.';
        } elseif ($id === $me) {
            $errors[] = 'Tidak boleh menghapus user yang sedang login.';
        } else {
            $repo->deleteById($id);
            flash_set('success', 'User berhasil dihapus.');
            header('Location: users.php'); // PRG
            exit;
        }
    }
}

$rows  = $repo->findAll();
$flash = flash_get();

require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/navbar.php';
?>

<div class="container container-narrow my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h3 class="mb-0">Manajemen User</h3>
    <span class="text-muted small">Khusus ADMIN</span>
  </div>

  <?php if ($flash): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
      <?= htmlspecialchars($flash['message']) ?>
    </div>
  <?php endif; ?>

  <?php if ($errors): ?>
    <div class="alert alert-danger">
      <ul class="mb-0">
        <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>

  <div class="card shadow-sm mb-3">
    <div class="card-body">
      <h5 class="card-title">Tambah User</h5>

      <form method="post" class="row g-2">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <div class="col-md-3">
          <label class="form-label">Username</label>
          <input class="form-control" name="username" maxlength="50" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Password</label>
          <input class="form-control" name="password" type="password" required>
        </div>

        <div class="col-md-3">
          <label class="form-label">Konfirmasi</label>
          <input class="form-control" name="password2" type="password" required>
        </div>

        <div class="col-md-2">
          <label class="form-label">Role</label>
          <select class="form-select" name="role" required>
            <option value="CASHIER">CASHIER</option>
            <option value="ADMIN">ADMIN</option>
          </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
          <button class="btn btn-dark w-100" type="submit">Simpan</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card shadow-sm">
    <div class="card-body">
      <h5 class="card-title">Daftar User</h5>

      <div class="table-responsive">
        <table class="table table-sm align-middle">
          <thead>
            <tr>
              <th>ID</th>
              <th>Username</th>
              <th>Role</th>
              <th>Created</th>
              <th class="text-end">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= (int)$r['id_user'] ?></td>
                <td><?= htmlspecialchars($r['username']) ?></td>
                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($r['role']) ?></span></td>
                <td class="text-muted small"><?= htmlspecialchars((string)$r['created_at']) ?></td>
                <td class="text-end">
                  <form method="post" class="d-inline" onsubmit="return confirm('Hapus user ini?')">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <input type="hidden" name="id" value="<?= (int)$r['id_user'] ?>">
                    <button class="btn btn-outline-danger btn-sm" type="submit">Hapus</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>

            <?php if (!$rows): ?>
              <tr><td colspan="5" class="text-muted">Belum ada user.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>