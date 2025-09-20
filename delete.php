<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
requireLogin();
requireRole('recruiter');

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    setFlash('error', 'Invalid document id.');
    header('Location: list.php');
    exit;
}

// Fetch record to get file paths
$stmt = $mysqli->prepare('SELECT project_book_path, certificate_path FROM students_documents WHERE id = ?');
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$stmt->close();

if (!$row) {
    setFlash('error', 'Record not found.');
    header('Location: list.php');
    exit;
}

$mysqli->begin_transaction();
try {
    $stmt = $mysqli->prepare('DELETE FROM students_documents WHERE id = ?');
    $stmt->bind_param('i', $id);
    if (!$stmt->execute()) {
        throw new Exception('Failed to delete record');
    }
    $stmt->close();

    // Remove files if exist
    $pb = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['../','..\\'], '', $row['project_book_path']);
    $cf = __DIR__ . DIRECTORY_SEPARATOR . str_replace(['../','..\\'], '', $row['certificate_path']);
    if (is_file($pb)) { @unlink($pb); }
    if (is_file($cf)) { @unlink($cf); }

    $mysqli->commit();
    setFlash('success', 'Document deleted.');
} catch (Throwable $e) {
    $mysqli->rollback();
    setFlash('error', 'Delete failed.');
}

header('Location: list.php');
exit;
?>

