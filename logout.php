<?php
declare(strict_types=1);

require __DIR__ . '/inc/init.php';
require __DIR__ . '/inc/helpers.php';

unset($_SESSION['user']);
session_regenerate_id(true);

flash_set('success', 'Anda telah logout.');
header('Location: login.php');
exit;