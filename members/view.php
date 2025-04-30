<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

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
?>

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

<?php require_once '../includes/footer.php'; ?>