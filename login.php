<?php
declare(strict_types=1);

require __DIR__ . '/inc/init.php';
require __DIR__ . '/inc/helpers.php';
require __DIR__ . '/Repository/UserRepository.php';

$repo = new UserRepository(Database::pdo());
$errors = [];

// Kalau sudah login, langsung ke dashboard
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

        if ($username === '' || $password === '') {
            $errors[] = 'Username dan password wajib diisi.';
        } else {
            $user = $repo->verifyLogin($username, $password);
            if ($user) {
                $_SESSION['user'] = $user; // id_user, username, role
                flash_set('success', 'Login berhasil.');
                header('Location: index.php');
                exit;
            }
            $errors[] = 'Login gagal. Username/password salah.';
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
          <h4 class="mb-1">Login</h4>
          <p class="text-muted small mb-3">Masuk untuk mengakses POS</p>

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

            <button class="btn btn-dark" type="submit">Login</button>
          </form>

          <hr class="my-3">
          <p class="text-muted small mb-0">
          </p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php require __DIR__ . '/inc/footer.php'; ?>