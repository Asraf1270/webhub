<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    die('Access denied');
}
require '../includes/seen.php';

$data = ['global' => [], 'users' => []];
if (saveSeenData($data)) {
    header('Location: dashboard.php?msg=Seen+data+cleared');
} else {
    header('Location: dashboard.php?msg=Failed+to+clear');
}
exit;