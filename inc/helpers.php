<?php
declare(strict_types=1);

function csrf_token(): string
{
    return (string)($_SESSION['_csrf'] ?? '');
}

function csrf_validate(string $token): bool
{
    $s = (string)($_SESSION['_csrf'] ?? '');
    return $s !== '' && hash_equals($s, $token);
}

/** Flash message untuk PRG */
function flash_set(string $type, string $message): void
{
    $_SESSION['_flash'] = ['type' => $type, 'message' => $message];
}

function flash_get(): ?array
{
    if (!isset($_SESSION['_flash'])) return null;
    $f = $_SESSION['_flash'];
    unset($_SESSION['_flash']);
    return $f;
}

/** Auth helpers (pakai kalau sudah login/role) */
function require_login(): void
{
    if (!isset($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}

function current_user(): ?array
{
    return $_SESSION['user'] ?? null;
}

function require_admin(): void
{
    require_login();
    if (($_SESSION['user']['role'] ?? '') !== 'ADMIN') {
        http_response_code(403);
        exit('Akses ditolak. Khusus ADMIN.');
    }
}