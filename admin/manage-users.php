<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php'); exit;
}

// Load current user role
$users = json_decode(file_get_contents('../data/users.json'), true);
$currentUser = null;
foreach ($users as $u) {
    if ($u['username'] === $_SESSION['user_id']) {
        $currentUser = $u;
        break;
    }
}
if (!$currentUser || $currentUser['role'] !== 'admin') {
    die('Access denied.');
}
?>
<!DOCTYPE html>
<html lang="en"><head><meta charset="UTF-8"><title>Manage Users</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="../style.css">
</head><body class="bg-light"><div class="container py-4">
<h2>Manage Users</h2>
<table class="table table-striped">
    <thead><tr><th>Username</th><th>Role</th><th>Created</th></tr></thead>
    <tbody>
    <?php foreach ($users as $u): ?>
        <tr>
            <td><?=htmlspecialchars($u['username'])?></td>
            <td><?=htmlspecialchars($u['role'])?></td>
            <td><?=htmlspecialchars($u['created'])?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<a href="dashboard.php" class="btn btn-secondary">Back</a>
</div></body></html>