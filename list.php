<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';
requireLogin();

$q = trim($_GET['q'] ?? '');

$sql = 'SELECT id, name, roll_no, email, phone, project_name, company_name, project_book_path, certificate_path, created_at, user_id FROM students_documents';
$params = [];
$types = '';
$where = [];

if ($q !== '') {
    $where[] = '(name LIKE ? OR roll_no LIKE ? OR email LIKE ? OR project_name LIKE ? OR company_name LIKE ?)';
    $like = '%' . $q . '%';
    array_push($params, $like, $like, $like, $like, $like);
    $types .= 'sssss';
}

// Role-based filtering: students see only their own docs
$u = currentUser();
if ($u && $u['role'] === 'student') {
    $where[] = 'user_id = ?';
    $params[] = (int)$u['id'];
    $types .= 'i';
}

if (!empty($where)) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}

$sql .= ' ORDER BY created_at DESC';

if ($types !== '') {
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $mysqli->query($sql);
}
?>
<!doctype html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Documents List</title>
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
					<a class="nav-link" href="upload.php">Upload</a>
				<?php endif; ?>
				<a class="nav-link active" href="list.php">View Documents</a>
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
	<?php if (isset($_GET['success'])): ?>
		<div class="alert alert-success"><?php echo htmlspecialchars($_GET['success']); ?></div>
	<?php endif; ?>
	<?php if (isset($_GET['error'])): ?>
		<div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
	<?php endif; ?>

	<div class="d-flex justify-content-between align-items-center mb-3">
		<h4 class="mb-0">Uploaded Documents</h4>
		<form class="d-flex" method="get">
			<input class="form-control me-2" type="search" placeholder="Search name, roll, email, project, company" name="q" value="<?php echo htmlspecialchars($q); ?>">
			<button class="btn btn-outline-primary" type="submit">Search</button>
		</form>
	</div>

	<div class="table-responsive">
		<table class="table table-striped table-bordered align-middle">
			<thead class="table-light">
				<tr>
					<th>#</th>
					<th>Name</th>
					<th>Roll No</th>
					<th>Email</th>
					<th>Phone</th>
					<th>Project</th>
					<th>Company</th>
					<th>Project Book</th>
					<th>Certificate</th>
					<th>Uploaded</th>
					<?php if ($u && $u['role'] === 'recruiter'): ?>
						<th>Actions</th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
			<?php if ($result && $result->num_rows > 0): $i = 1; ?>
				<?php while ($row = $result->fetch_assoc()): ?>
				<tr>
					<td><?php echo $i++; ?></td>
					<td><?php echo htmlspecialchars($row['name']); ?></td>
					<td><?php echo htmlspecialchars($row['roll_no']); ?></td>
					<td><a href="mailto:<?php echo htmlspecialchars($row['email']); ?>"><?php echo htmlspecialchars($row['email']); ?></a></td>
					<td><a href="tel:<?php echo htmlspecialchars($row['phone']); ?>"><?php echo htmlspecialchars($row['phone']); ?></a></td>
					<td><?php echo htmlspecialchars($row['project_name']); ?></td>
					<td><?php echo htmlspecialchars($row['company_name']); ?></td>
					<td><a class="btn btn-sm btn-outline-primary" href="<?php echo htmlspecialchars($row['project_book_path']); ?>" target="_blank">Download</a></td>
					<td><a class="btn btn-sm btn-outline-secondary" href="<?php echo htmlspecialchars($row['certificate_path']); ?>" target="_blank">Download</a></td>
					<td><?php echo htmlspecialchars($row['created_at']); ?></td>
					<?php if ($u && $u['role'] === 'recruiter'): ?>
						<td>
							<a class="btn btn-sm btn-danger" href="delete.php?id=<?php echo (int)$row['id']; ?>" onclick="return confirm('Delete this document? This cannot be undone.');">Delete</a>
						</td>
					<?php endif; ?>
				</tr>
				<?php endwhile; ?>
			<?php else: ?>
				<tr><td colspan="10" class="text-center py-4">No records found.</td></tr>
			<?php endif; ?>
			</tbody>
		</table>
	</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

