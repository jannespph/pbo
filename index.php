<?php
declare(strict_types=1);

require __DIR__ . '/inc/init.php';
require __DIR__ . '/inc/helpers.php';

require_login();

$flash = flash_get();

require __DIR__ . '/inc/header.php';
require __DIR__ . '/inc/navbar.php';
?>

<div class="container container-narrow my-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Dashboard</h3>
        <span class="text-muted small">Menu master data POS</span>
    </div>

    <?php if ($flash): ?>
        <div class="alert alert-<?= htmlspecialchars($flash['type']) ?>">
            <?= htmlspecialchars($flash['message']) ?>
        </div>
    <?php endif; ?>

    <div class="row g-3">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Kategori</h5>
                    <p class="card-text text-muted">Kelola kategori produk.</p>
                    <a href="kategori.php" class="btn btn-dark btn-sm">Buka</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Satuan</h5>
                    <p class="card-text text-muted">Kelola satuan produk.</p>
                    <a href="satuan.php" class="btn btn-dark btn-sm">Buka</a>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Produk</h5>
                    <p class="card-text text-muted">Kelola data produk.</p>
                    <a href="produk.php" class="btn btn-dark btn-sm">Buka</a>
                </div>
            </div>
        </div>

        <?php if (strtolower(current_user()['role'] ?? '') === 'admin'): ?>
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title">Users</h5>
                    <p class="card-text text-muted">Kelola akun kasir/admin.</p>
                    <a href="users.php" class="btn btn-dark btn-sm">Buka</a>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/inc/footer.php'; ?>