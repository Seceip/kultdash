<?php
require_once __DIR__ . '/includes/auth.php';
$_SESSION = [];
session_destroy();
header('Location: ' . (LOGIN_ATIVO ? 'login.php' : 'index.php'));
exit;
