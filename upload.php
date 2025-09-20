<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
requireLogin();
requireRole('student');
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Upload Internship Documents</title>
	<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
	<link rel="stylesheet" href="styles.css">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
	<div class="container">
		<a class="navbar-brand" href="index.php">Internship Repository</a>
		<div class="navbar-nav ms-auto">
			<?php if ($u = currentUser()): ?>
				<?php if ($u['role'] === 'student'): ?>
					<a class="nav-link active" href="upload.php">Upload</a>
				<?php endif; ?>
				<a class="nav-link" href="list.php">View Documents</a>
				<span class="navbar-text text-white ms-3">Hello, <?php echo htmlspecialchars($u['name']); ?> (<?php echo htmlspecialchars($u['role']); ?>)</span>
				<a class="nav-link" href="logout.php">Logout</a>
			<?php else: ?>
				<a class="nav-link" href="signup.php">Sign Up</a>
				<a class="nav-link" href="login.php">Login</a>
			<?php endif; ?>
		</div>
	</div>
</nav>

<div class="container">
	<div class="row justify-content-center">
		<div class="col-lg-8">
			<div class="card shadow-sm">
				<div class="card-header bg-primary text-white">
					<h5 class="mb-0">Upload Internship Details</h5>
				</div>
				<div class="card-body">
					<form id="uploadForm" action="process_upload.php" method="post" enctype="multipart/form-data" novalidate>
						<div class="row g-3">
							<div class="col-md-6">
								<label for="name" class="form-label">Name</label>
								<input type="text" class="form-control" id="name" name="name" required maxlength="100">
							</div>
							<div class="col-md-6">
								<label for="roll_no" class="form-label">Roll No</label>
								<input type="text" class="form-control" id="roll_no" name="roll_no" required maxlength="50">
							</div>
							<div class="col-md-6">
								<label for="email" class="form-label">Email</label>
								<input type="email" class="form-control" id="email" name="email" required maxlength="150">
							</div>
							<div class="col-md-6">
								<label for="phone" class="form-label">Phone</label>
								<input type="tel" class="form-control" id="phone" name="phone" required pattern="[0-9\-\+\s]{7,20}" maxlength="20">
							</div>
							<div class="col-md-6">
								<label for="project_name" class="form-label">Project Name</label>
								<input type="text" class="form-control" id="project_name" name="project_name" required maxlength="200">
							</div>
							<div class="col-md-6">
								<label for="company_name" class="form-label">Internship Company Name</label>
								<input type="text" class="form-control" id="company_name" name="company_name" required maxlength="200">
							</div>
							<div class="col-md-6">
								<label for="project_book" class="form-label">Internship Project Book (PDF)</label>
								<input class="form-control" type="file" id="project_book" name="project_book" accept="application/pdf" required>
							</div>
							<div class="col-md-6">
								<label for="certificate" class="form-label">Certificate (PDF/Image)</label>
								<input class="form-control" type="file" id="certificate" name="certificate" accept="application/pdf,image/*" required>
							</div>
						</div>
						<div class="d-flex gap-2 mt-4">
							<button class="btn btn-primary" type="submit">Submit</button>
							<a href="index.php" class="btn btn-secondary">Cancel</a>
						</div>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<script>
(function(){
	const form = document.getElementById('uploadForm');
	form.addEventListener('submit', function(e){
		if (!form.checkValidity()) {
			e.preventDefault();
			e.stopPropagation();
		}
		form.classList.add('was-validated');
		const maxSize = 10 * 1024 * 1024; // 10MB
		const pb = document.getElementById('project_book').files[0];
		const cert = document.getElementById('certificate').files[0];
		if ((pb && pb.size > maxSize) || (cert && cert.size > maxSize)) {
			alert('Each file must be 10 MB or smaller.');
			e.preventDefault();
			e.stopPropagation();
		}
	});
})();
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

