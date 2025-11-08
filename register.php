<?php
session_start();
require_once 'includes/auth.php';

// If already logged in → go home
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header('Location: index.php');
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm'] ?? '';

    // ---- VALIDATION ----
    if ($username === '' || $password === '' || $confirm === '') {
        $errors[] = 'All fields are required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        // Load users
        $file = __DIR__ . '/data/users.json';
        $users = file_exists($file) ? json_decode(file_get_contents($file), true) : [];

        // Check duplicate
        foreach ($users as $u) {
            if ($u['username'] === $username) {
                $errors[] = 'Username already taken.';
                break;
            }
        }

        // ---- SAVE NEW USER ----
        if (empty($errors)) {
            $users[] = [
                'username' => $username,
                'password' => password_hash($password, PASSWORD_DEFAULT),
                'role'     => 'user',               // default role
                'created'  => date('Y-m-d H:i:s')
            ];
            file_put_contents($file, json_encode($users, JSON_PRETTY_PRINT));

            // Auto-login
            $_SESSION['user_id'] = $username;
            $_SESSION['loggedin'] = true;
            header('Location: index.php');
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register – My Website Hub</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-4">Create Account</h3>

                    <?php if (!empty($errors)): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach ($errors as $e): ?>
                                    <li><?=htmlspecialchars($e)?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="POST" novalidate>
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" name="username" class="form-control" required minlength="3"
                                   value="<?=htmlspecialchars($_POST['username'] ?? '')?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required minlength="6">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Confirm Password</label>
                            <input type="password" name="confirm" class="form-control" required minlength="6">
                        </div>
                        <button type="submit" class="btn btn-success w-100">Register</button>
                    </form>

                    <p class="text-center mt-3 small">
                        Already have an account?
                        <a href="login.php">Login here</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>