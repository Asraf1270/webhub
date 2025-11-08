<?php
session_start();
require_once '../includes/auth.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$websites = json_decode(file_get_contents('../data/websites.json'), true) ?: [];
$site = null;
$id = $_GET['id'] ?? 0;

foreach ($websites as $w) {
    if ($w['id'] == $id) {
        $site = $w;
        break;
    }
}

if (!$site) {
    header('Location: dashboard.php?msg=Website+not+found');
    exit;
}

$error = $success = '';

if ($_POST) {
    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $url         = trim($_POST['url'] ?? '');
    $thumbnail   = trim($_POST['thumbnail'] ?? '');
    $category    = trim($_POST['category'] ?? '');

    if ($title === '' || $url === '' || $thumbnail === '' || $category === '') {
        $error = 'All fields are required.';
    } elseif (!filter_var($url, FILTER_VALIDATE_URL)) {
        $error = 'Invalid URL.';
    } else {
        foreach ($websites as &$w) {
            if ($w['id'] == $id) {
                $w['title']       = $title;
                $w['description'] = $description;
                $w['url']         = $url;
                $w['thumbnail']   = $thumbnail;
                $w['category']    = $category;
                break;
            }
        }

        if (file_put_contents('../data/websites.json', json_encode($websites, JSON_PRETTY_PRINT))) {
            $success = 'Website updated successfully!';
        } else {
            $error = 'Failed to save changes.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Website – Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../style.css">
    <style>
        body { background: linear-gradient(135deg, #0f0c29, #302b63); min-height: 100vh; font-family: 'Inter', sans-serif; }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            padding: 2rem;
        }
        .form-control, .form-select {
            background: rgba(255, 255, 255, 0.15);
            border: none;
            color: white;
            border-radius: 12px;
        }
        .form-control::placeholder { color: rgba(255, 255, 255, 0.6); }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.25);
            box-shadow: 0 0 0 3px rgba(168, 85, 247, 0.4);
            color: white;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 600;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
    </style>
</head>
<body class="text-white">

<div class="container py-5">
    <div class="glass-card mx-auto" style="max-width: 700px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0">Edit Website</h3>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill">← Back</a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label">Title</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($site['title']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3" required><?= htmlspecialchars($site['description']) ?></textarea>
            </div>
            <div class="mb-3">
                <label class="form-label">URL</label>
                <input type="url" name="url" class="form-control" value="<?= htmlspecialchars($site['url']) ?>" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Thumbnail URL</label>
                <input type="url" name="thumbnail" class="form-control" value="<?= htmlspecialchars($site['thumbnail']) ?>" required>
                <div class="mt-2">
                    <img src="<?= htmlspecialchars($site['thumbnail']) ?>" alt="Preview" class="img-thumbnail" style="max-height: 100px;" onerror="this.src='https://via.placeholder.com/300x150?text=No+Image'">
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Category</label>
                <input type="text" name="category" class="form-control" value="<?= htmlspecialchars($site['category']) ?>" required>
            </div>
            <div class="d-grid">
                <button type="submit" class="btn btn-primary">Update Website</button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Bootstrap form validation
    (() => {
        'use strict';
        const forms = document.querySelectorAll('.needs-validation');
        Array.from(forms).forEach(form => {
            form.addEventListener('submit', event => {
                if (!form.checkValidity()) {
                    event.preventDefault();
                    event.stopPropagation();
                }
                form.classList.add('was-validated');
            }, false);
        });
    })();
</script>
</body>
</html>