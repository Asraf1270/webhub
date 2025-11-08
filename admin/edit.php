<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php');
    exit;
}

$id = intval($_GET['id']);
$file = '../data/websites.json';
$data = json_decode(file_get_contents($file), true);
$site = null;
foreach ($data as $s) {
    if ($s['id'] == $id) {
        $site = $s;
        break;
    }
}
if (!$site) {
    header('Location: dashboard.php');
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
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['id'] == $id) {
                $data[$i]['title'] = $title;
                $data[$i]['url'] = $url;
                $data[$i]['description'] = $description;
                $data[$i]['thumbnail'] = $thumbnail;
                $data[$i]['category'] = $category;
                break;
            }
        }
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
    <title>Edit Website</title>
    <link rel="stylesheet" href="../style.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>
    <div class="container">
        <h2>Edit Website</h2>
        <form method="POST">
            <input type="text" name="title" value="<?php echo htmlspecialchars($site['title']); ?>" required>
            <input type="url" name="url" value="<?php echo htmlspecialchars($site['url']); ?>" required>
            <textarea name="description" required><?php echo htmlspecialchars($site['description']); ?></textarea>
            <input type="url" name="thumbnail" value="<?php echo htmlspecialchars($site['thumbnail']); ?>" required>
            <input type="text" name="category" value="<?php echo htmlspecialchars($site['category']); ?>" required>
            <button type="submit">Update</button>
        </form>
        <?php if ($error): ?><p style="color:red;"><?php echo $error; ?></p><?php endif; ?>
        <a href="dashboard.php">Back</a>
    </div>
</body>
</html>