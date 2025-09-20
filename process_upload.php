<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
requireLogin();
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: upload.php');
    exit;
}

function sanitize($value) {
    return trim($value ?? '');
}

$name = sanitize($_POST['name'] ?? '');
$rollNo = sanitize($_POST['roll_no'] ?? '');
$email = sanitize($_POST['email'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$projectName = sanitize($_POST['project_name'] ?? '');
$companyName = sanitize($_POST['company_name'] ?? '');

$errors = [];
if ($name === '' || $rollNo === '' || $email === '' || $phone === '' || $projectName === '' || $companyName === '') {
    $errors[] = 'All fields are required.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email address.';
}
if (!preg_match('/^[0-9\-\+\s]{7,20}$/', $phone)) {
    $errors[] = 'Invalid phone number.';
}

if (!isset($_FILES['project_book']) || !isset($_FILES['certificate'])) {
    $errors[] = 'Both files are required.';
}

$maxSize = 10 * 1024 * 1024; // 10MB

// Validate files
$projectBook = $_FILES['project_book'] ?? null;
$certificate = $_FILES['certificate'] ?? null;

if (!$projectBook || $projectBook['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Error uploading Project Book file.';
}
if (!$certificate || $certificate['error'] !== UPLOAD_ERR_OK) {
    $errors[] = 'Error uploading Certificate file.';
}

// Basic MIME/type checks
$finfo = new finfo(FILEINFO_MIME_TYPE);
$allowedProjectMime = ['application/pdf'];
$allowedCertMime = ['application/pdf', 'image/jpeg', 'image/png', 'image/webp'];

if ($projectBook && $projectBook['size'] > $maxSize) {
    $errors[] = 'Project Book exceeds 10MB.';
}
if ($certificate && $certificate['size'] > $maxSize) {
    $errors[] = 'Certificate exceeds 10MB.';
}

if (empty($errors)) {
    $projMime = $finfo->file($projectBook['tmp_name']);
    $certMime = $finfo->file($certificate['tmp_name']);
    if (!in_array($projMime, $allowedProjectMime, true)) {
        $errors[] = 'Project Book must be a PDF.';
    }
    if (!in_array($certMime, $allowedCertMime, true)) {
        $errors[] = 'Certificate must be PDF/JPG/PNG/WEBP.';
    }
}

if (!empty($errors)) {
    $msg = urlencode(implode(' ', $errors));
    header('Location: upload.php?error=' . $msg);
    exit;
}

// Prepare upload directories
$baseDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads';
$projectDir = $baseDir . DIRECTORY_SEPARATOR . 'projects';
$certDir = $baseDir . DIRECTORY_SEPARATOR . 'certificates';
if (!is_dir($projectDir)) { @mkdir($projectDir, 0777, true); }
if (!is_dir($certDir)) { @mkdir($certDir, 0777, true); }

// Generate safe filenames
function generateSafeFilename($prefix, $originalName) {
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $slug = preg_replace('/[^a-zA-Z0-9\-]+/', '-', strtolower($prefix));
    $slug = trim($slug, '-');
    $uniq = bin2hex(random_bytes(6));
    return $slug . '-' . $uniq . ($ext ? ('.' . strtolower($ext)) : '');
}

$projectFilename = generateSafeFilename($rollNo . '-project', $projectBook['name']);
$certFilename = generateSafeFilename($rollNo . '-certificate', $certificate['name']);

$projectDest = $projectDir . DIRECTORY_SEPARATOR . $projectFilename;
$certDest = $certDir . DIRECTORY_SEPARATOR . $certFilename;

// Move files securely
if (!move_uploaded_file($projectBook['tmp_name'], $projectDest)) {
    header('Location: upload.php?error=' . urlencode('Failed to save Project Book.'));
    exit;
}
if (!move_uploaded_file($certificate['tmp_name'], $certDest)) {
    @unlink($projectDest);
    header('Location: upload.php?error=' . urlencode('Failed to save Certificate.'));
    exit;
}

// Store relative paths for linking
$projectRel = 'uploads/projects/' . basename($projectDest);
$certRel = 'uploads/certificates/' . basename($certDest);

// Insert into DB
$user = currentUser();
$userId = $user ? (int)$user['id'] : null;

$stmt = $mysqli->prepare('INSERT INTO students_documents 
    (name, roll_no, email, phone, project_name, company_name, project_book_path, certificate_path, user_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)');
if (!$stmt) {
    @unlink($projectDest);
    @unlink($certDest);
    header('Location: upload.php?error=' . urlencode('Database error: prepare failed.'));
    exit;
}
$stmt->bind_param('ssssssssi', $name, $rollNo, $email, $phone, $projectName, $companyName, $projectRel, $certRel, $userId);
if (!$stmt->execute()) {
    @unlink($projectDest);
    @unlink($certDest);
    $stmt->close();
    header('Location: upload.php?error=' . urlencode('Database error: insert failed.'));
    exit;
}
$stmt->close();

header('Location: list.php?success=' . urlencode('Upload successful.'));
exit;
?>

