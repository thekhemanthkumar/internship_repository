<?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
<?php require_once __DIR__ . '/db.php'; ?>
<?php
// Compute live metrics
$counts = [
	'documents' => 0,
	'students' => 0,
	'recruiters' => 0,
	'my_docs' => 0,
];

// Total documents
$res = $mysqli->query('SELECT COUNT(*) AS c FROM students_documents');
if ($res) { $row = $res->fetch_assoc(); $counts['documents'] = (int)$row['c']; $res->free(); }

// Users by role
$res = $mysqli->query("SELECT role, COUNT(*) AS c FROM users GROUP BY role");
if ($res) {
	while ($row = $res->fetch_assoc()) {
		if ($row['role'] === 'student') { $counts['students'] = (int)$row['c']; }
		if ($row['role'] === 'recruiter') { $counts['recruiters'] = (int)$row['c']; }
	}
	$res->free();
}

// My documents for logged-in student
if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student') {
	$uid = (int)$_SESSION['user']['id'];
	$stmt = $mysqli->prepare('SELECT COUNT(*) AS c FROM students_documents WHERE user_id = ?');
	$stmt->bind_param('i', $uid);
	$stmt->execute();
	$r = $stmt->get_result()->fetch_assoc();
	$counts['my_docs'] = (int)$r['c'];
	$stmt->close();
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Internship Repository</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
	<div class="container">
		<a class="navbar-brand" href="index.php">Internship Repository</a>
		<div class="navbar-nav ms-auto">
			<a class="nav-link" href="upload.php">Upload</a>
			<a class="nav-link" href="list.php">View Documents</a>
			<?php if (!empty($_SESSION['user'])): ?>
				<span class="navbar-text text-white ms-3">Hello, <?php echo htmlspecialchars($_SESSION['user']['name']); ?> (<?php echo htmlspecialchars($_SESSION['user']['role']); ?>)</span>
				<a class="nav-link" href="logout.php">Logout</a>
			<?php else: ?>
				<a class="nav-link" href="signup.php">Sign Up</a>
				<a class="nav-link" href="login.php">Login</a>
			<?php endif; ?>
		</div>
	</div>
</nav>

<section class="hero-section py-5 position-relative overflow-hidden">
	<div class="container position-relative">
		<div class="row align-items-center">
			<div class="col-lg-6">
				<h1 class="display-5 fw-bold text-gradient mb-3">Centralize Internship Documents with Ease</h1>
				<p class="lead text-muted mb-4">Students securely upload, recruiters review effortlessly. All in one place with fast search and clean organization.</p>
				<div class="d-flex gap-3 flex-wrap">
					<?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
						<a class="btn btn-primary btn-lg shadow lift" href="upload.php">Upload Documents</a>
					<?php endif; ?>
					<a class="btn btn-outline-light btn-lg shadow lift" href="list.php">View Documents</a>
				</div>
				<?php if (empty($_SESSION['user'])): ?>
					<p class="mt-3 small text-white-50">New here? <a class="link-light" href="signup.php">Create an account</a> or <a class="link-light" href="login.php">login</a>.</p>
				<?php endif; ?>
			</div>
            <div class="col-lg-6 mt-4 mt-lg-0">
				<div class="hero-visual card-glow p-4 rounded-4 bg-blur">
					<div class="row g-3">
						<div class="col-6">
							<div class="stat-tile">
                                <div class="stat-value" data-target="<?php echo (int)$counts['documents']; ?>">0</div>
                                <div class="stat-label">Documents</div>
							</div>
						</div>
						<div class="col-6">
							<div class="stat-tile">
                                <div class="stat-value" data-target="<?php echo (int)$counts['students']; ?>">0</div>
                                <div class="stat-label">Students</div>
							</div>
						</div>
                        <div class="col-6">
							<div class="stat-tile">
                                <div class="stat-value" data-target="<?php echo (int)$counts['recruiters']; ?>">0</div>
                                <div class="stat-label">Recruiters</div>
							</div>
						</div>
                        <?php if (!empty($_SESSION['user']) && $_SESSION['user']['role'] === 'student'): ?>
                        <div class="col-6">
                            <div class="stat-tile">
                                <div class="stat-value" data-target="<?php echo (int)$counts['my_docs']; ?>">0</div>
                                <div class="stat-label">My Documents</div>
                            </div>
                        </div>
                        <?php endif; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="wave-container">
		<svg class="waves" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="currentColor" fill-opacity="1" d="M0,160L60,165.3C120,171,240,181,360,181.3C480,181,600,171,720,176C840,181,960,203,1080,197.3C1200,192,1320,160,1380,144L1440,128L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path></svg>
	</div>
</section>

<section class="features-section py-5">
	<div class="container">
		<div class="row g-4">
			<div class="col-md-4">
				<div class="feature-card p-4 h-100">
					<h5>Secure Uploads</h5>
					<p class="text-muted mb-0">Validated file types, safe storage paths, and user-linked records.</p>
				</div>
			</div>
			<div class="col-md-4">
				<div class="feature-card p-4 h-100">
					<h5>Smart Access</h5>
					<p class="text-muted mb-0">Students see only their own submissions. Recruiters see everything.</p>
				</div>
			</div>
			<div class="col-md-4">
				<div class="feature-card p-4 h-100">
					<h5>Quick Reviews</h5>
					<p class="text-muted mb-0">Search, filter, and download documents instantly with one click.</p>
				</div>
			</div>
		</div>
	</div>
</section>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Count-up animation when in view
function animateValue(el) {
	const target = parseInt(el.getAttribute('data-target'), 10) || 0;
	let current = 0;
	const duration = 1600;
	const start = performance.now();
	function tick(now){
		const progress = Math.min((now - start) / duration, 1);
		current = Math.floor(progress * target);
		el.textContent = current.toLocaleString();
		if (progress < 1) requestAnimationFrame(tick);
	}
	requestAnimationFrame(tick);
}

const observer = new IntersectionObserver((entries)=>{
	entries.forEach(entry=>{
		if (entry.isIntersecting) {
			animateValue(entry.target);
			observer.unobserve(entry.target);
		}
	});
}, { threshold: 0.6 });

document.querySelectorAll('.stat-value').forEach(el=>observer.observe(el));
</script>
</body>
</html>

