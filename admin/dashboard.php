<?php
session_start();
require_once '../includes/auth.php';

// Only admins
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

$users = json_decode(file_get_contents('../data/users.json'), true) ?: [];
$websites = json_decode(file_get_contents('../data/websites.json'), true) ?: [];
$seenData = json_decode(file_get_contents('../data/seen.json'), true) ?: ['global' => [], 'users' => []];
$totalViews = array_sum(array_column($websites, 'views'));
$totalUsers = count($users);
$totalWebsites = count($websites);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel – Website Hub</title>

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;600;700&family=Inter:wght@400;500&display=swap" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../style.css">
    <style>
        .admin-body { background: linear-gradient(135deg, #0f0c29, #302b63); min-height: 100vh; }
        .glass-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            border-radius: 20px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border-radius: 16px;
            padding: 1.5rem;
            text-align: center;
            transition: transform 0.3s;
        }
        .stat-card:hover { transform: translateY(-8px); }
        .nav-link.active { color: #a855f7 !important; font-weight: 600; }
        .table-glass { background: rgba(255,255,255,0.05); border-radius: 12px; overflow: hidden; }
        .search-input { background: rgba(255,255,255,0.15); border: none; color: white; }
        .search-input:focus { background: rgba(255,255,255,0.25); box-shadow: 0 0 0 3px rgba(168,85,247,0.4); }

        .progress { background: rgba(255,255,255,0.1); border-radius: 10px; overflow: hidden; }
.progress-bar { font-weight: 600; font-size: 0.8rem; line-height: 20px; }
.badge { font-size: 0.85rem; }
    </style>
</head>
<body class="admin-body text-white">

<div class="container py-5">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="h3 fw-bold text-gradient">Admin Panel</h1>
        <div>
            <span class="me-3">Hi, <?= htmlspecialchars($_SESSION['user_id']) ?></span>
            <a href="logout.php" class="btn btn-outline-light btn-sm rounded-pill">Logout</a>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-people display-4"></i>
                <h3><?= $totalUsers ?></h3>
                <p>Total Users</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-globe display-4"></i>
                <h3><?= $totalWebsites ?></h3>
                <p>Websites</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-eye display-4"></i>
                <h3><?= number_format($totalViews) ?></h3>
                <p>Total Views</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <i class="bi bi-check-circle display-4"></i>
                <h3><?= count($seenData['global']) + array_sum(array_map('count', $seenData['users'])) ?></h3>
                <p>Seen Entries</p>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-4 glass-card p-1" id="adminTabs">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#websites">Websites</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#users">Users</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#analytics">Analytics</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#seen">Seen Data</a>
        </li>
    </ul>

    <div class="tab-content">

        <!-- Websites Tab -->
        <div class="tab-pane fade show active" id="websites">
            <div class="d-flex justify-content-between mb-3">
                <input type="text" id="websiteSearch" class="form-control search-input w-50" placeholder="Search websites…">
                <a href="add_website.php" class="btn btn-primary rounded-pill">+ Add Website</a>
            </div>
            <div class="table-responsive table-glass">
                <table class="table table-dark table-hover align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Category</th>
                            <th>Views</th>
                            <th>Added</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="websiteTable">
                        <?php foreach ($websites as $w): ?>
                        <tr>
                            <td><?= $w['id'] ?></td>
                            <td><?= htmlspecialchars($w['title']) ?></td>
                            <td><span class="badge bg-info"><?= htmlspecialchars($w['category']) ?></span></td>
                            <td><?= $w['views'] ?></td>
                            <td><?= date('M j, Y', strtotime($w['date_added'])) ?></td>
                            <td>
                                <a href="edit_website.php?id=<?= $w['id'] ?>" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                                <a href="delete_website.php?id=<?= $w['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Users Tab -->
        <div class="tab-pane fade" id="users">
            <div class="d-flex justify-content-between mb-3">
                <input type="text" id="userSearch" class="form-control search-input w-50" placeholder="Search users…">
                <a href="add_user.php" class="btn btn-success rounded-pill">+ Add User</a>
            </div>
            <div class="table-responsive table-glass">
                <table class="table table-dark table-hover">
                    <thead>
                        <tr>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTable">
                        <?php foreach ($users as $u): ?>
                        <tr>
                            <td><?= htmlspecialchars($u['username']) ?></td>
                            <td>
                                <form method="POST" action="update_role.php" class="d-inline">
                                    <input type="hidden" name="username" value="<?= $u['username'] ?>">
                                    <select name="role" onchange="this.form.submit()" class="form-select form-select-sm d-inline w-auto">
                                        <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>User</option>
                                        <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                    </select>
                                </form>
                            </td>
                            <td><?= date('M j, Y', strtotime($u['created'])) ?></td>
                            <td>
                                <a href="delete_user.php?username=<?= urlencode($u['username']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user?')"><i class="bi bi-trash"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Analytics Tab -->
        <div class="tab-pane fade" id="analytics">
            <div class="row">
                <div class="col-md-8">
                    <canvas id="viewsChart" height="100"></canvas>
                </div>
                <div class="col-md-4">
                    <div class="glass-card p-4">
                        <h5>Top Websites</h5>
                        <ol>
                            <?php
                            $top = $websites;
                            usort($top, fn($a,$b) => $b['views'] - $a['views']);
                            foreach (array_slice($top, 0, 5) as $t): ?>
                            <li><?= htmlspecialchars($t['title']) ?> (<?= $t['views'] ?> views)</li>
                            <?php endforeach; ?>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <!-- Seen Analytics Tab -->
<div class="tab-pane fade" id="seen">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5>Real-Time Seen Analytics</h5>
        <div>
            <button onclick="clearAllSeen()" class="btn btn-outline-danger btn-sm rounded-pill">Clear All</button>
            <button onclick="refreshSeen()" class="btn btn-outline-light btn-sm rounded-pill ms-2">Refresh Now</button>
        </div>
    </div>

    <!-- Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="stat-card p-3 text-center">
                <i class="bi bi-eye"></i>
                <h4 id="totalSeenCount">0</h4>
                <small>Total Seen Events</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3 text-center">
                <i class="bi bi-person-check"></i>
                <h4 id="uniqueSeenUsers">0</h4>
                <small>Unique Users</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3 text-center">
                <i class="bi bi-globe"></i>
                <h4 id="globalSeenCount">0</h4>
                <small>Guest Seen</small>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card p-3 text-center">
                <i class="bi bi-arrow-repeat"></i>
                <h4><span id="lastUpdate">Never</span></h4>
                <small>Last Update</small>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="glass-card p-4 mb-4">
        <canvas id="seenChart" height="100"></canvas>
    </div>

    <!-- Table -->
    <div class="table-responsive table-glass">
        <table class="table table-dark table-hover" id="seenTable">
            <thead>
                <tr>
                    <th>Website</th>
                    <th>Seen By</th>
                    <th>% of Users</th>
                    <th>Views</th>
                    <th>Seen Ratio</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Search
    document.getElementById('websiteSearch').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#websiteTable tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });
    document.getElementById('userSearch').addEventListener('input', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#userTable tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(q) ? '' : 'none';
        });
    });

    // Chart
    const ctx = document.getElementById('viewsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column(array_slice($websites, 0, 10), 'title')) ?>,
            datasets: [{
                label: 'Views',
                data: <?= json_encode(array_column(array_slice($websites, 0, 10), 'views')) ?>,
                borderColor: '#a855f7',
                backgroundColor: 'rgba(168, 85, 247, 0.2)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, plugins: { legend: { labels: { color: '#e0e0e0' } } }, scales: { x: { ticks: { color: '#e0e0e0' } }, y: { ticks: { color: '#e0e0e0' } } } }
    });

    // Clear Seen
    function clearAllSeen() {
        if (!confirm('Clear ALL seen data? This affects every user.')) return;
        fetch('clear_seen.php', { method: 'POST' })
            .then(() => location.reload());
    }

// Real-Time Seen Analytics
let seenChart = null;

function refreshSeen() {
    fetch('get_seen_analytics.php')
        .then(r => r.json())
        .then(data => {
            updateSeenStats(data);
            updateSeenChart(data.analytics);
            updateSeenTable(data.analytics);
            document.getElementById('lastUpdate').textContent = data.lastUpdate;
        });
}

function updateSeenStats(data) {
    document.getElementById('totalSeenCount').textContent = data.totalSeen;
    document.getElementById('uniqueSeenUsers').textContent = data.uniqueUsers;
    document.getElementById('globalSeenCount').textContent = data.globalSeen;
}

function updateSeenChart(analytics) {
    const ctx = document.getElementById('seenChart').getContext('2d');
    const labels = analytics.slice(0, 8).map(a => a.title.substring(0, 20) + (a.title.length > 20 ? '...' : ''));
    const values = analytics.slice(0, 8).map(a => a.seen);

    if (seenChart) seenChart.destroy();

    seenChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Users Who Have Seen',
                data: values,
                backgroundColor: 'rgba(168, 85, 247, 0.6)',
                borderColor: '#a855f7',
                borderWidth: 2,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { labels: { color: '#e0e0e0' } },
                tooltip: { backgroundColor: 'rgba(0,0,0,0.8)' }
            },
            scales: {
                x: { ticks: { color: '#e0e0e0' } },
                y: { ticks: { color: '#e0e0e0' }, beginAtZero: true }
            }
        }
    });
}

function updateSeenTable(analytics) {
    const tbody = document.querySelector('#seenTable tbody');
    tbody.innerHTML = '';
    analytics.forEach(a => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><strong>${escapeHtml(a.title)}</strong></td>
            <td><span class="badge bg-success">${a.seen}</span></td>
            <td><div class="progress" style="height: 20px;"><div class="progress-bar bg-info" style="width: ${a.percent}%">${a.percent}%</div></div></td>
            <td>${a.views}</td>
            <td><span class="badge ${a.ratio > 1 ? 'bg-danger' : 'bg-warning'}">${a.ratio}x</span></td>
        `;
        tbody.appendChild(row);
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Auto-refresh every 10 seconds
setInterval(refreshSeen, 10000);
refreshSeen(); // Initial load
</script>
</body>
</html>