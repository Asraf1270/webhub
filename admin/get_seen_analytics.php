<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit;
}

require '../includes/seen.php';

$websites = json_decode(file_get_contents('../data/websites.json'), true) ?: [];
$users = json_decode(file_get_contents('../data/users.json'), true) ?: [];
$seenData = getSeenData();

$totalUsers = count($users);
$analytics = [];

foreach ($websites as $site) {
    $siteId = $site['id'];
    $seenCount = 0;

    // Count in global
    if (in_array($siteId, $seenData['global'])) $seenCount++;

    // Count in users
    foreach ($seenData['users'] as $userSeen) {
        if (in_array($siteId, $userSeen)) $seenCount++;
    }

    $percent = $totalUsers > 0 ? round(($seenCount / $totalUsers) * 100, 1) : 0;
    $ratio = $site['views'] > 0 ? round($seenCount / $site['views'], 2) : 0;

    $analytics[] = [
        'id' => $siteId,
        'title' => $site['title'],
        'seen' => $seenCount,
        'percent' => $percent,
        'views' => $site['views'],
        'ratio' => $ratio
    ];
}

// Sort by seen count
usort($analytics, fn($a, $b) => $b['seen'] - $a['seen']);

$globalSeen = count($seenData['global']);
$userSeenTotal = array_sum(array_map('count', $seenData['users']));
$uniqueUsers = count(array_filter($seenData['users'], fn($arr) => !empty($arr)));

echo json_encode([
    'analytics' => $analytics,
    'totalSeen' => $globalSeen + $userSeenTotal,
    'uniqueUsers' => $uniqueUsers,
    'globalSeen' => $globalSeen,
    'lastUpdate' => date('H:i:s')
]);