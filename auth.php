<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$script = basename(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$public = [
    'login.php',
    'signup.php',
    'logout.php',
    'robots.txt', 'sitemap.xml'
];
if ($script === '' || $script === '/') {
    $script = 'index.html';
}
if (!in_array($script, $public, true)) {
    if (!isset($_SESSION['user']) || empty($_SESSION['user'])) {
        header('Location: login.php');
        exit;
    }
}
?>