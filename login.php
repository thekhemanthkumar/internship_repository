<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');

    $errors = [];
    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email address.';
    }

    if (!verifyCaptcha($captcha)) {
        $errors[] = 'Captcha answer is incorrect.';
    }

    if (empty($errors)) {
        $stmt = $mysqli->prepare('SELECT id, name, email, password_hash, role FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'name' => $user['name'],
                'email' => $user['email'],
                'role' => $user['role'],
            ];
            setFlash('success', 'Welcome back, ' . $user['name'] . '!');
            header('Location: index.php');
            exit;
        } else {
            setFlash('error', 'Invalid credentials.');
            header('Location: login.php');
            exit;
        }
    } else {
        setFlash('error', implode(' ', $errors));
        header('Location: login.php');
        exit;
    }
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Login</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
	<div class="container">
		<a class="navbar-brand" href="index.php">Internship Repository</a>
		<div class="navbar-nav">
			<a class="nav-link" href="signup.php">Sign Up</a>
			<a class="nav-link active" href="login.php">Login</a>
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
				<div class="card-header bg-primary text-white"><h5 class="mb-0">Login</h5></div>
				<div class="card-body">
					<?php generateCaptcha(); ?>
					<form method="post" class="row g-3">
						<div class="col-12">
							<label class="form-label" for="email">Email</label>
							<input class="form-control" type="email" id="email" name="email" required>
						</div>
						<div class="col-12">
							<label class="form-label" for="password">Password</label>
							<input class="form-control" type="password" id="password" name="password" required>
						</div>
						<div class="col-12">
							<label class="form-label" for="captcha">Solve to verify: <?php echo htmlspecialchars(getCaptchaQuestion()); ?></label>
							<input class="form-control" type="text" id="captcha" name="captcha" inputmode="numeric" required>
						</div>
						<div class="col-12 d-flex gap-2">
							<button class="btn btn-primary" type="submit">Login</button>
							<a class="btn btn-outline-secondary" href="signup.php">Create account</a>
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

