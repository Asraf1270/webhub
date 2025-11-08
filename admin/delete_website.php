<?php
session_start();
require_once '../includes/auth.php';

if ($_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: dashboard.php?msg=No+ID');
    exit;
}

$id = (int)$_GET['id'];
$websites = json_decode(file_get_contents('../data/websites.json'), true) ?: [];
$found = false;

foreach ($websites as $key => $w) {
    if ($w['id'] == $id) {
        unset($websites[$key]);
        $found = true;
        break;
    }
}

if ($found) {
    $websites = array_values($websites); // reindex
    if (file_put_contents('../data/websites.json', json_encode($websites, JSON_PRETTY_PRINT))) {
        // Also remove from seen.json
        require '../includes/seen.php';
        $seen = getSeenData();
        $seen['global'] = array_values(array_diff($seen['global'], [$id]));
        foreach ($seen['users'] as &$userSeen) {
            $userSeen = array_values(array_diff($userSeen, [$id]));
        }
        saveSeenData($seen);

        header('Location: dashboard.php?msg=Website+deleted');
    } else {
        header('Location: dashboard.php?msg=Failed+to+delete');
    }
} else {
    header('Location: dashboard.php?msg=Website+not+found');
}
exit;