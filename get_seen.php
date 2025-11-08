<?php
session_start();
require_once 'includes/seen.php';
header('Content-Type: application/json');

$userId = $_SESSION['user_id'] ?? 'guest';
$data = getSeenData();

$seen = ($userId === 'guest') ? $data['global'] : ($data['users'][$userId] ?? []);

echo json_encode($seen);