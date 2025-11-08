<?php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = intval($_POST['id']);
    $file = 'data/websites.json';
    if (!file_exists($file)) {
        echo json_encode(['success' => false]);
        exit;
    }
    $data = json_decode(file_get_contents($file), true);
    foreach ($data as &$site) {
        if ($site['id'] == $id) {
            $site['views'] = ($site['views'] ?? 0) + 1;
            break;
        }
    }
    file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}