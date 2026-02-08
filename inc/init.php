<?php
declare(strict_types=1);

session_start();

require __DIR__ . '/../config.php';
require __DIR__ . '/../Database.php';

if (!isset($_SESSION['_csrf'])) {
    $_SESSION['_csrf'] = bin2hex(random_bytes(32));
}