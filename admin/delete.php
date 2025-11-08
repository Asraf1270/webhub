<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('Location: login.php');
    exit;
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $file = '../data/websites.json';
    $data = json_decode(file_get_contents($file), true);
    $newData = array_filter($data, function($site) use ($id) {
        return $site['id'] != $id;
    });
    file_put_contents($file, json_encode(array_values($newData), JSON_PRETTY_PRINT));
}
// admin/delete.php  (replace the last lines)
file_put_contents($file, json_encode(array_values($newData), JSON_PRETTY_PRINT));
header('Location: dashboard.php?msg=Website+deleted+successfully&type=success');
exit;