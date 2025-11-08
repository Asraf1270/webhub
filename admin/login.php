<?php
session_start();

// Prevent logged-in users from seeing login again
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Both fields are required.';
    } else {
        $users = json_decode(file_get_contents('../data/users.json'), true);
        $found = false;

        foreach ($users as $user) {
            if ($user['username'] === $username 
                && password_verify($password, $user['password'])
                && ($user['role'] ?? 'user') === 'admin') {
                $_SESSION['user_id'] = $username;
                $_SESSION['loggedin'] = true;
                $_SESSION['role'] = 'admin';
                $found = true;
                break;
            }
        }

        if ($found) {
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid admin credentials.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login – My Website Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4 text-danger">Admin Login</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Admin Username</label>
                            <input type="text" name="username" class="form-control" required
                                   value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">Login as Admin</button>
                    </form>

                    <div class="text-center mt-3 small">
                        <p><a href="../login.php" class="text-muted">← Back to User Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>