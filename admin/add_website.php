<?php
session_start();
require_once '../includes/auth.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
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
        $error = 'Please enter a valid URL (e.g., https://example.com).';
    } elseif (!filter_var($thumbnail, FILTER_VALIDATE_URL)) {
        $error = 'Thumbnail must be a valid image URL.';
    } else {
        $websites = json_decode(file_get_contents('../data/websites.json'), true) ?: [];
        
        // Generate new ID
        $newId = $websites ? max(array_column($websites, 'id')) + 1 : 1;

        $newWebsite = [
            'id'          => $newId,
            'title'       => $title,
            'description' => $description,
            'url'         => $url,
            'thumbnail'   => $thumbnail,
            'category'    => $category,
            'views'       => 0,
            'date_added'  => date('Y-m-d H:i:s')
        ];

        $websites[] = $newWebsite;

        if (file_put_contents('../data/websites.json', json_encode($websites, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))) {
            $success = 'Website added successfully!';
            // Reset form
            $_POST = [];
        } else {
            $error = 'Failed to save website. Check file permissions.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Website – Admin Panel</title>

    <!-- Fonts & Icons -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom Style -->
    <link rel="stylesheet" href="../style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f0c29, #302b63);
            min-height: 100vh;
            font-family: 'Inter', sans-serif;
            color: #e0e0e0;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
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
            padding: 12px 16px;
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
            padding: 12px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
        }
        .preview-img {
            max-height: 120px;
            border-radius: 12px;
            border: 2px solid rgba(255, 255, 255, 0.2);
            margin-top: 10px;
        }
        .alert {
            border-radius: 12px;
            font-weight: 500;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="glass-card mx-auto" style="max-width: 700px;">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="mb-0 text-gradient">Add New Website</h3>
            <a href="dashboard.php" class="btn btn-outline-light btn-sm rounded-pill">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success d-flex align-items-center">
                <i class="bi bi-check-circle-fill me-2"></i>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="needs-validation" novalidate>
            <div class="mb-3">
                <label class="form-label fw-semibold">Website Title</label>
                <input type="text" name="title" class="form-control" placeholder="e.g., GitHub" value="<?= $_POST['title'] ?? '' ?>" required>
                <div class="invalid-feedback">Please enter a title.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Description</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Brief description of the website..." required><?= $_POST['description'] ?? '' ?></textarea>
                <div class="invalid-feedback">Please enter a description.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Website URL</label>
                <input type="url" name="url" class="form-control" placeholder="https://github.com" value="<?= $_POST['url'] ?? '' ?>" required>
                <div class="invalid-feedback">Please enter a valid URL.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Thumbnail Image URL</label>
                <input type="url" name="thumbnail" id="thumbnail" class="form-control" placeholder="https://example.com/image.jpg" value="<?= $_POST['thumbnail'] ?? '' ?>" required onchange="previewImage()">
                <div class="invalid-feedback">Please enter a valid image URL.</div>
                <img id="preview" src="" alt="Preview" class="preview-img d-none">
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Category</label>
                <input type="text" name="category" class="form-control" placeholder="e.g., Development, Design, Tools" value="<?= $_POST['category'] ?? '' ?>" required>
                <div class="invalid-feedback">Please enter a category.</div>
            </div>

            <div class="d-grid">
                <button type="submit" class="btn btn-primary btn-lg">
                    <i class="bi bi-plus-circle me-2"></i> Add Website
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Live thumbnail preview
    function previewImage() {
        const url = document.getElementById('thumbnail').value;
        const preview = document.getElementById('preview');
        if (url) {
            preview.src = url;
            preview.classList.remove('d-none');
            preview.onerror = () => {
                preview.src = 'https://via.placeholder.com/300x150/1a1a2e/ffffff?text=Invalid+Image';
            };
        } else {
            preview.classList.add('d-none');
        }
    }

    // Bootstrap validation
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