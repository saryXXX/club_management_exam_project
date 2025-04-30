<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit();
}

// Check if ID parameter exists
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid payment ID";
    header('Location: payment_list.php');
    exit();
}

$payment_id = intval($_GET['id']);

// Fetch payment data with member information
try {
    $stmt = $pdo->prepare("
        SELECT p.*, CONCAT(m.first_name, ' ', m.last_name) as member_name 
        FROM payments p 
        LEFT JOIN members m ON p.member_id = m.member_id 
        WHERE p.payment_id = ?
    ");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error'] = "Payment not found";
        header('Location: payment_list.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching payment data: " . $e->getMessage();
    header('Location: payment_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Payment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Payment Details</h1>

        <div class="payment-details">
            <p><strong>Member:</strong> <?php echo htmlspecialchars($payment['member_name']); ?></p>
            <p><strong>Amount:</strong> $<?php echo number_format($payment['amount'], 2); ?></p>
            <p><strong>Payment Date:</strong> <?php echo htmlspecialchars($payment['payment_date']); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars(ucfirst($payment['payment_method'])); ?></p>
            <p><strong>Period Covered:</strong> <?php echo htmlspecialchars($payment['period_covered']); ?></p>
        </div>

        <div class="actions">
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="edit.php?id=<?php echo $payment['payment_id']; ?>" class="btn">Edit</a>
            <a href="delete.php?id=<?php echo $payment['payment_id']; ?>" class="btn-danger" 
               onclick="return confirm('Are you sure you want to delete this payment?')">Delete</a>
            <?php endif; ?>
            <a href="payment_list.php" class="btn-secondary">Back to List</a>
        </div>
    </div>
</body>
</html>