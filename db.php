<?php
// Database connection for internship_repo
// Configure here or via environment variables

// Optional local override file (not committed). Create config.php to set $db_* variables.
@include __DIR__ . '/config.php';

$db_host = isset($db_host) ? $db_host : (getenv('DB_HOST') ?: 'localhost');
$db_user = isset($db_user) ? $db_user : (getenv('DB_USER') ?: 'root');
$db_pass = isset($db_pass) ? $db_pass : (getenv('DB_PASS') ?: '');
$db_name = isset($db_name) ? $db_name : (getenv('DB_NAME') ?: 'internship_repo');
$db_port = isset($db_port) ? $db_port : (getenv('DB_PORT') ?: 3306);

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name, (int)$db_port);
if ($mysqli->connect_errno) {
    http_response_code(500);
    die('Database connection failed: ' . $mysqli->connect_error);
}

if (!$mysqli->set_charset('utf8mb4')) {
    http_response_code(500);
    die('Error loading character set utf8mb4: ' . $mysqli->error);
}

?>

