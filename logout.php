<?php
require_once 'config.php';
require_once 'functions.php';

if (isLoggedIn()) {
    logActivity($pdo, $_SESSION['user_id'], 'logout', 'Kullanıcı çıkış yaptı.');
    destroyUserSession($pdo);
} else {
    session_unset();
    session_destroy();
}

header('Location: login.php');
exit;
?>