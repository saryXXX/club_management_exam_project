<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

try {
    $stmt = $pdo->query("
        SELECT p.*, m.first_name, m.last_name 
        FROM payments p
        JOIN members m ON p.member_id = m.member_id
        ORDER BY p.payment_date DESC
    ");
    $payments = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching payments: " . $e->getMessage());
}
?>

<h2>Payments</h2>
<a href="create.php" class="btn btn-primary mb-3">Record New Payment</a>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Payment ID</th>
            <th>Member</th>
            <th>Amount</th>
            <th>Date</th>
            <th>Method</th>
            <th>Period Covered</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($payments as $payment): ?>
        <tr>
            <td><?= htmlspecialchars($payment['payment_id']) ?></td>
            <td><?= htmlspecialchars($payment['first_name']) ?> <?= htmlspecialchars($payment['last_name']) ?></td>
            <td>$<?= number_format($payment['amount'], 2) ?></td>
            <td><?= htmlspecialchars($payment['payment_date']) ?></td>
            <td><?= ucfirst(str_replace('_', ' ', $payment['payment_method'])) ?></td>
            <td><?= htmlspecialchars($payment['period_covered']) ?></td>
            <td>
                <a href="view.php?id=<?= $payment['payment_id'] ?>" class="btn btn-info btn-sm">View</a>
                <a href="edit.php?id=<?= $payment['payment_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete.php?id=<?= $payment['payment_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>