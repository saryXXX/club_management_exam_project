<?php
session_start();

// Check if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

require_once 'includes/db_connection.php';
require_once 'includes/header.php';

// Get user role for permission checks
$role = $_SESSION['role'];
$name = $_SESSION['name'] ?? 'User';

// Get counts for dashboard
try {
    // Member count - visible to all roles
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM members WHERE active = 1");
    $activeMembers = $stmt->fetch()['count'];

    if ($role === 'admin' || $role === 'staff') {
        // Staff count - only for admin and staff
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM staff");
        $staffCount = $stmt->fetch()['count'];

        // Equipment needing replacement
        $stmt = $pdo->query("SELECT COUNT(*) as count FROM equipment WHERE condition_status IN ('poor', 'broken')");
        $badEquipment = $stmt->fetch()['count'];

        // Recent payments - only for admin
        if ($role === 'admin') {
            $stmt = $pdo->query("SELECT SUM(amount) as total FROM payments WHERE payment_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)");
            $recentPayments = $stmt->fetch()['total'];
        }
    }

    // Upcoming events - visible to all
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM events WHERE event_date >= CURDATE()");
    $upcomingEvents = $stmt->fetch()['count'];

    // Recent events - visible to all
    $stmt = $pdo->query("
        SELECT e.*, t.team_name 
        FROM events e
        LEFT JOIN teams t ON e.team_id = t.team_id
        WHERE e.event_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
        ORDER BY e.event_date DESC
        LIMIT 5
    ");
    $recentEvents = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching dashboard data: " . $e->getMessage());
}
?>

<!-- Custom CSS for Purple Gradient Theme -->
<style>
    body {
        background-color: #f4f8fb;
        font-family: 'Arial', sans-serif;
    }

    .dashboard header h1 {
        color: #2c3e50;
        margin-bottom: 20px;
    }

    .card {
        border-radius: 8px;
        margin-bottom: 20px;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
        color: white;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0px 5px 15px rgba(0, 0, 0, 0.1);
    }

    .card-body {
        padding: 20px;
    }

    .card-title {
        font-size: 1.25rem;
        font-weight: bold;
    }

    .card-text {
        font-size: 2.5rem;
        font-weight: 700;
    }

    .btn-danger {
        background-color: #e74c3c;
        border: none;
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 5px;
        color: #fff;
        text-decoration: none;
    }

    .btn-danger:hover {
        background-color: #c0392b;
    }

    /* Purple Gradient (soft) */
    .bg-primary {
        background: linear-gradient(135deg,#b6b6b6, #b6b6b6) !important;
        color: white;
    }

    .bg-success {
        background: linear-gradient(135deg, #b6b6b6, #b6b6b6) !important;
        color: white;
    }

    .bg-info {
        background: linear-gradient(135deg, #b6b6b6, #b6b6b6) !important;
        color: white;
    }

    .bg-warning {
        background: linear-gradient(135deg, #b6b6b6, #b6b6b6) !important;
        color: white;
    }

    .bg-danger {
        background: linear-gradient(135deg, #b6b6b6, #b6b6b6) !important;
        color: white;
    }

    .bg-secondary {
        background: linear-gradient(135deg, #b6b6b6, #b6b6b6) !important;
        color: white;
    }

    .list-group-item {
        background-color: #fff;
        border: 1px solid #ddd;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .list-group-item:hover {
        background-color: #ecf0f1;
    }

    .text-white {
        color: #fff !important;
    }

    .text-white:hover {
        color: #ecf0f1 !important;
    }
</style>

<div class="dashboard">
    <header>
        <h1>Welcome, <?php echo htmlspecialchars($name); ?></h1>
    </header>

    <div class="row mb-4">
        <!-- Active Members Card -->
        <div class="col-md-4">
            <div class="card bg-primary">
                <div class="card-body">
                    <h5 class="card-title">Active Members</h5>
                    <p class="card-text"><?= $activeMembers ?></p>
                    <a href="members/member_list.php" class="text-white">View Members</a>
                </div>
            </div>
        </div>

        <?php if ($role === 'admin' || $role === 'staff'): ?>
        <!-- Staff Members Card -->
        <div class="col-md-4">
            <div class="card bg-success">
                <div class="card-body">
                    <h5 class="card-title">Staff Members</h5>
                    <p class="card-text"><?= $staffCount ?></p>
                    <a href="staff/staff_list.php" class="text-white">View Staff</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Upcoming Events Card -->
        <div class="col-md-4">
            <div class="card bg-info">
                <div class="card-body">
                    <h5 class="card-title">Upcoming Events</h5>
                    <p class="card-text"><?= $upcomingEvents ?></p>
                    <a href="events/event_list.php" class="text-white">View Events</a>
                </div>
            </div>
        </div>
    </div>

    <?php if ($role === 'admin' || $role === 'staff'): ?>
    <div class="row mb-4">
        <!-- Equipment Issues Card -->
        <div class="col-md-4">
            <div class="card bg-warning">
                <div class="card-body">
                    <h5 class="card-title">Equipment Issues</h5>
                    <p class="card-text"><?= $badEquipment ?></p>
                    <a href="equipment/equipment_list.php" class="text-white">View Equipment</a>
                </div>
            </div>
        </div>

        <?php if ($role === 'admin'): ?>
        <!-- Recent Revenue Card -->
        <div class="col-md-4">
            <div class="card bg-danger">
                <div class="card-body">
                    <h5 class="card-title">Recent Revenue</h5>
                    <p class="card-text">$<?= number_format($recentPayments, 2) ?></p>
                    <a href="payments/payment_list.php" class="text-white">View Payments</a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Active Teams Card -->
        <div class="col-md-4">
            <div class="card bg-secondary">
                <div class="card-body">
                    <h5 class="card-title">Active Teams</h5>
                    <?php
                    $teamCount = $pdo->query("SELECT COUNT(*) FROM teams")->fetchColumn();
                    ?>
                    <p class="card-text"><?= $teamCount ?></p>
                    <a href="teams/team_list.php" class="text-white">View Teams</a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>



    <h3>Recent Events</h3>
    <?php if (count($recentEvents) > 0): ?>
        <div class="list-group">
            <?php foreach ($recentEvents as $event): ?>
            <a href="events/view.php?id=<?= $event['event_id'] ?>" class="list-group-item">
                <div class="d-flex w-100 justify-content-between">
                    <h5 class="mb-1"><?= htmlspecialchars($event['event_name']) ?></h5>
                    <small><?= date('M j, Y', strtotime($event['event_date'])) ?></small>
                </div>
                <p class="mb-1">Team: <?= $event['team_name'] ? htmlspecialchars($event['team_name']) : 'General' ?></p>
            </a>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <p>No recent events found.</p>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
