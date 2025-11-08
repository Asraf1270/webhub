<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $url = trim($_POST['url'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $thumbnail = trim($_POST['thumbnail'] ?? '');
    $category = trim($_POST['category'] ?? '');

    if (empty($title) || empty($url) || empty($description) || empty($thumbnail) || empty($category)) {
        $error = 'All fields are required';
    } else {
        $file = '../data/websites.json';
        $data = json_decode(file_get_contents($file), true);
        $newId = count($data) > 0 ? max(array_column($data, 'id')) + 1 : 1;
        $newSite = [
            'id' => $newId,
            'title' => $title,
            'url' => $url,
            'description' => $description,
            'thumbnail' => $thumbnail,
            'category' => $category,
            'date_added' => date('Y-m-d'),
            'views' => 0
        ];
        $data[] = $newSite;
        file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
        header('Location: dashboard.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Website</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Add New Website</h2>
        <form method="POST" class="row g-3 needs-validation" novalidate>
    <div class="col-md-6">
        <label class="form-label">Title</label>
        <input type="text" name="title" class="form-control" required>
        <div class="invalid-feedback">Required</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">URL</label>
        <input type="url" name="url" class="form-control" required>
        <div class="invalid-feedback">Valid URL required</div>
    </div>
    <div class="col-12">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3" required></textarea>
        <div class="invalid-feedback">Required</div>
    </div>
    <div class="col-md-6">
        <label class="form-label">Thumbnail URL</label>
        <input type="url" name="thumbnail" class="form-control" required>
    </div>
    <div class="col-md-6">
        <label class="form-label">Category</label>
        <input type="text" name="category" class="form-control" required>
    </div>
    <div class="col-12">
        <button type="submit" class="btn btn-success">Add Website</button>
        <a href="dashboard.php" class="btn btn-secondary ms-2">Cancel</a>
    </div>
</form>

<script>
  // Bootstrap client-side validation
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
        <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
        <a href="dashboard.php">Back</a>
    </div>
</body>
</html>