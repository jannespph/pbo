<?php
declare(strict_types=1);

require __DIR__ . '/inc/init.php';
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/Repository/UserRepository.php';

$repo = new UserRepository(Database::pdo());
$errors = [];

// Kalau sudah login, redirect ke dashboard
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF check
    if (!csrf_validate((string)($_POST['csrf'] ?? ''))) {
        $errors[] = 'CSRF token tidak valid.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = (string)($_POST['password'] ?? '');
        $passwordConfirm = (string)($_POST['password_confirm'] ?? '');
        $role = 'USER'; // default role

        // Validasi input
        if ($username === '' || $password === '' || $passwordConfirm === '') {
            $errors[] = 'Semua field wajib diisi.';
        } elseif ($password !== $passwordConfirm) {
            $errors[] = 'Password dan konfirmasi password tidak sama.';
        } elseif ($repo->findByUsername($username)) {
            $errors[] = 'Username sudah digunakan.';
        } else {
            // Insert user baru
            $id = $repo->insert($username, $password, $role);
            flash_set('success', 'Registrasi berhasil. Silakan login.');
            header('Location: login.php');
            exit;
        }
    }
}

$flash = flash_get();

require __DIR__ . '/inc/header.php';
?>

<div class="container container-narrow my-5">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h4 class="mb-1">Registrasi</h4>
          <p class="text-muted small mb-3">Buat akun baru untuk mengakses POS</p>

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

          <form method="post" class="vstack gap-2">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div>
              <label class="form-label">Username</label>
              <input class="form-control" name="username" required autofocus>
            </div>

            <div>
              <label class="form-label">Password</label>
              <input class="form-control" name="password" type="password" required>
            </div>

            <div>
              <label class="form-label">Konfirmasi Password</label>
              <input class="form-control" name="password_confirm" type="password" required>
            </div>

            <button class="btn btn-dark" type="submit">Daftar</button>
          </form>

          <hr class="my-3">
          <p class="text-muted small mb-0">
            Sudah punya akun? <a href="login.php">Login di sini</a>.
          </p>
        </div>
      </div>
    </div>
  </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>
