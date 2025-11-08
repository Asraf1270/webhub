<?php
require '../includes/seen.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['site_id'])) {
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$siteId = (int)$_POST['site_id'];
$data = getSeenData();

$userId = defined('CURRENT_USER_ID') ? CURRENT_USER_ID : 'guest';

if ($userId === 'guest') {
    if (!in_array($siteId, $data['global'], true)) {
        $data['global'][] = $siteId;
    }
} else {
    $data['users'][$userId] ??= [];
    if (!in_array($siteId, $data['users'][$userId], true)) {
        $data['users'][$userId][] = $siteId;
    }
}

if (saveSeenData($data)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Failed to save']);
}