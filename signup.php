<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
@include __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'student';
    $captcha = trim($_POST['captcha'] ?? '');

    $errors = [];
    if ($name === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }
    if ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    }
    if (!in_array($role, ['student','recruiter'], true)) {
        $errors[] = 'Invalid role.';
    }
    // Enforce student email domain if configured
    if ($role === 'student' && !empty($student_email_domain ?? '')) {
        $domain = strtolower(substr(strrchr($email, '@') ?: '', 1));
        if ($domain !== strtolower($student_email_domain)) {
            $errors[] = 'Students must use their college email (' . htmlspecialchars($student_email_domain) . ').';
        }
    }
    // Captcha check
    if (!verifyCaptcha($captcha)) {
        $errors[] = 'Captcha answer is incorrect.';
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $errors[] = 'Email already registered.';
        }
        $stmt->close();
    }

    if (empty($errors)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $mysqli->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (?, ?, ?, ?)');
        $stmt->bind_param('ssss', $name, $email, $hash, $role);
        if ($stmt->execute()) {
            setFlash('success', 'Signup successful. Please login.');
            header('Location: login.php');
            exit;
        } else {
            $errors[] = 'Failed to create account.';
        }
        $stmt->close();
    }

    if (!empty($errors)) {
        setFlash('error', implode(' ', $errors));
        header('Location: signup.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Sign Up</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
	<div class="container">
		<a class="navbar-brand" href="index.php">Internship Repository</a>
		<div class="navbar-nav">
			<a class="nav-link active" href="signup.php">Sign Up</a>
			<a class="nav-link" href="login.php">Login</a>
		</div>
	</div>
</nav>

<div class="container">
	<?php if ($msg = getFlash('error')): ?>
		<div class="alert alert-danger"><?php echo htmlspecialchars($msg); ?></div>
	<?php endif; ?>
	<?php if ($msg = getFlash('success')): ?>
		<div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
	<?php endif; ?>

	<div class="row justify-content-center">
		<div class="col-lg-6">
			<div class="card shadow-sm">
				<div class="card-header bg-primary text-white"><h5 class="mb-0">Create Account</h5></div>
				<div class="card-body">
					<?php generateCaptcha(); ?>
					<form method="post" class="row g-3">
						<div class="col-12">
							<label class="form-label" for="name">Full Name</label>
							<input class="form-control" type="text" id="name" name="name" required>
						</div>
						<div class="col-12">
							<label class="form-label" for="email">Email</label>
							<input class="form-control" type="email" id="email" name="email" required>
						</div>
						<div class="col-md-6">
							<label class="form-label" for="password">Password</label>
							<input class="form-control" type="password" id="password" name="password" required minlength="6">
						</div>
						<div class="col-md-6">
							<label class="form-label" for="confirm_password">Confirm Password</label>
							<input class="form-control" type="password" id="confirm_password" name="confirm_password" required minlength="6">
						</div>
						<div class="col-12">
							<label class="form-label" for="role">Role</label>
							<select class="form-select" id="role" name="role" required>
								<option value="student">Student</option>
								<option value="recruiter">Recruiter</option>
							</select>
						</div>
						<div class="col-12">
							<label class="form-label" for="captcha">Solve to verify: <?php echo htmlspecialchars(getCaptchaQuestion()); ?></label>
							<input class="form-control" type="text" id="captcha" name="captcha" inputmode="numeric" required>
						</div>
						<div class="col-12 d-flex gap-2">
							<button class="btn btn-primary" type="submit">Sign Up</button>
							<a class="btn btn-outline-secondary" href="login.php">Have an account? Login</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

