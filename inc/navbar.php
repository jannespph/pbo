<?php
declare(strict_types=1);

$user = current_user();
$role = $user['role'] ?? '';
?>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand fw-semibold" href="index.php"><?= htmlspecialchars(APP_NAME ?? 'POS') ?></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item"><a class="nav-link" href="kategori.php">Kategori</a></li>
                <li class="nav-item"><a class="nav-link" href="satuan.php">Satuan</a></li>
                <li class="nav-item"><a class="nav-link" href="produk.php">Produk</a></li>
                <?php if ($role === 'ADMIN'): ?>
                    <li class="nav-item"><a class="nav-link" href="users.php">Users</a></li>
                <?php endif; ?>
            </ul>

            <div class="d-flex align-items-center gap-2">
                <?php if ($user): ?>
                    <span class="text-white-50 small">
                        <?= htmlspecialchars($user['username']) ?> <span class="badge text-bg-secondary"><?= htmlspecialchars($role) ?></span>
                    </span>
                    <a class="btn btn-outline-light btn-sm" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="btn btn-outline-light btn-sm" href="login.php">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>