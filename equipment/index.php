<?php
session_start();
require_once('../includes/db_connection.php');

// If no user is logged in, redirect to login page
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if the member ID is provided in the URL
if (isset($_GET['id'])) {
    // Fetch the member details, teams, and payment history
    $member_id = $_GET['id'];

    try {
        // Fetch member data
        $stmt = $pdo->prepare("SELECT * FROM members WHERE member_id = ?");
        $stmt->execute([$member_id]);
        $member = $stmt->fetch();

        if (!$member) {
            header("Location: index.php");
            exit();
        }

        // Fetch teams the member belongs to
        $stmt = $pdo->prepare("
            SELECT t.team_name, t.sport_type 
            FROM teams t
            JOIN member_team mt ON t.team_id = mt.team_id
            WHERE mt.member_id = ?
        ");
        $stmt->execute([$member_id]);
        $teams = $stmt->fetchAll();

        // Fetch payments made by the member
        $stmt = $pdo->prepare("SELECT * FROM payments WHERE member_id = ? ORDER BY payment_date DESC");
        $stmt->execute([$member_id]);
        $payments = $stmt->fetchAll();
    } catch (PDOException $e) {
        die("Error fetching member details: " . $e->getMessage());
    }
}
else {
    // Fetch equipment statistics and other information when no member ID is provided
    try {
        $total_stmt = $pdo->query("SELECT COUNT(*) FROM equipment");
        $total_equipment = $total_stmt->fetchColumn();

        $condition_stmt = $pdo->query("
            SELECT condition_status, COUNT(*) as count 
            FROM equipment 
            GROUP BY condition_status
        ");
        $condition_stats = $condition_stmt->fetchAll(PDO::FETCH_ASSOC);

        $attention_stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM equipment 
            WHERE condition_status IN ('poor', 'broken')
        ");
        $needs_attention = $attention_stmt->fetchColumn();

        $unassigned_stmt = $pdo->query("
            SELECT COUNT(*) 
            FROM equipment 
            WHERE assigned_to IS NULL
        ");
        $unassigned = $unassigned_stmt->fetchColumn();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error fetching statistics: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Equipment Management</title>

    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #2c3e50;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        h1, h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .stats-grid, .condition-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .stat-card, .condition-card {
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.2s ease;
        }

        .stat-card:hover, .condition-card:hover {
            transform: translateY(-5px);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #007bff;
        }

        .condition-card.excellent { border-left: 5px solid #28a745; }
        .condition-card.good { border-left: 5px solid #17a2b8; }
        .condition-card.fair { border-left: 5px solid #ffc107; }
        .condition-card.poor { border-left: 5px solid #fd7e14; }
        .condition-card.broken { border-left: 5px solid #dc3545; }

        .action-buttons {
            text-align: center;
            margin-top: 30px;
        }

        .btn {
            padding: 10px 20px;
            margin: 5px;
            display: inline-block;
            background-color: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn.primary {
            background-color: #28a745;
        }

        .btn.primary:hover {
            background-color: #218838;
        }

        @media (max-width: 768px) {
            .stat-number {
                font-size: 2rem;
            }

            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if (isset($member)): ?>
            <h2>Member Details</h2>
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($member['first_name']) ?> <?= htmlspecialchars($member['last_name']) ?></h5>
                    <p class="card-text">
                        <strong>Email:</strong> <?= htmlspecialchars($member['email']) ?><br>
                        <strong>Phone:</strong> <?= htmlspecialchars($member['phone']) ?><br>
                        <strong>Join Date:</strong> <?= htmlspecialchars($member['join_date']) ?><br>
                        <strong>Membership Type:</strong> <?= ucfirst(htmlspecialchars($member['membership_type'])) ?><br>
                        <strong>Status:</strong> <?= $member['active'] ? 'Active' : 'Inactive' ?>
                    </p>
                    <a href="edit.php?id=<?= $member['member_id'] ?>" class="btn btn-warning">Edit</a>
                    <a href="index.php" class="btn btn-secondary">Back to List</a>
                </div>
            </div>

            <h4>Teams</h4>
            <?php if (count($teams) > 0): ?>
                <ul class="list-group mb-4">
                    <?php foreach ($teams as $team): ?>
                    <li class="list-group-item">
                        <?= htmlspecialchars($team['team_name']) ?> (<?= htmlspecialchars($team['sport_type']) ?>)
                    </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>This member doesn't belong to any teams.</p>
            <?php endif; ?>

            <h4>Payment History</h4>
            <?php if (count($payments) > 0): ?>
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Payment ID</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Method</th>
                            <th>Period Covered</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $payment): ?>
                        <tr>
                            <td><?= htmlspecialchars($payment['payment_id']) ?></td>
                            <td>$<?= number_format($payment['amount'], 2) ?></td>
                            <td><?= htmlspecialchars($payment['payment_date']) ?></td>
                            <td><?= ucfirst(str_replace('_', ' ', htmlspecialchars($payment['payment_method']))) ?></td>
                            <td><?= htmlspecialchars($payment['period_covered']) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>No payment history found.</p>
            <?php endif; ?>
        <?php else: ?>
            <h1>Equipment Management</h1>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Equipment</h3>
                    <p class="stat-number"><?php echo $total_equipment; ?></p>
                </div>

                <div class="stat-card">
                    <h3>Needs Attention</h3>
                    <p class="stat-number"><?php echo $needs_attention; ?></p>
                </div>

                <div class="stat-card">
                    <h3>Unassigned</h3>
                    <p class="stat-number"><?php echo $unassigned; ?></p>
                </div>
            </div>

            <div class="condition-summary">
                <h2>Equipment by Condition</h2>
                <div class="condition-grid">
                    <?php foreach ($condition_stats as $stat): ?>
                        <div class="condition-card <?php echo strtolower($stat['condition_status']); ?>">
                            <h4><?php echo ucfirst($stat['condition_status']); ?></h4>
                            <p class="stat-number"><?php echo $stat['count']; ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="action-buttons">
                <?php if ($_SESSION['role'] === 'admin'): ?>
                    <a href="create.php" class="btn primary">Add New Equipment</a>
                <?php endif; ?>
                <a href="index.php" class="btn">View All Equipment</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
