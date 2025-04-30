<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $member_id = intval($_POST['member_id']);
        $amount = floatval($_POST['amount']);
        $payment_date = trim($_POST['payment_date']);
        $payment_method = trim($_POST['payment_method']);
        $period_covered = trim($_POST['period_covered']);

        // Validate inputs
        if (empty($member_id) || empty($amount) || empty($payment_date) || 
            empty($payment_method) || empty($period_covered)) {
            throw new Exception("All fields are required");
        }

        if ($amount <= 0) {
            throw new Exception("Amount must be greater than zero");
        }

        // Update payment record
        $stmt = $pdo->prepare("UPDATE payments SET member_id = ?, amount = ?, payment_date = ?, 
                              payment_method = ?, period_covered = ? WHERE payment_id = ?");
        $stmt->execute([$member_id, $amount, $payment_date, $payment_method, $period_covered, $payment_id]);

        $_SESSION['success'] = "Payment updated successfully";
        header('Location: payment_list.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating payment: " . $e->getMessage();
    }
}

// Fetch payment data
try {
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = ?");
    $stmt->execute([$payment_id]);
    $payment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        $_SESSION['error'] = "Payment not found";
        header('Location: payment_list.php');
        exit();
    }

    // Fetch all members for dropdown
    $member_stmt = $pdo->query("SELECT member_id, first_name, last_name FROM members");
    $members = $member_stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Edit Payment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Payment</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="member_id">Member:</label>
                <select id="member_id" name="member_id" required>
                    <option value="">Select a member</option>
                    <?php foreach ($members as $member): ?>
                        <option value="<?php echo $member['member_id']; ?>" 
                                <?php echo ($payment['member_id'] == $member['member_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="amount">Amount:</label>
                <input type="number" id="amount" name="amount" step="0.01" min="0.01" 
                       value="<?php echo htmlspecialchars($payment['amount']); ?>" required>
            </div>

            <div class="form-group">
                <label for="payment_date">Payment Date:</label>
                <input type="date" id="payment_date" name="payment_date" 
                       value="<?php echo htmlspecialchars($payment['payment_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="payment_method">Payment Method:</label>
                <select id="payment_method" name="payment_method" required>
                    <option value="">Select payment method</option>
                    <option value="cash" <?php echo ($payment['payment_method'] == 'cash') ? 'selected' : ''; ?>>Cash</option>
                    <option value="credit" <?php echo ($payment['payment_method'] == 'credit') ? 'selected' : ''; ?>>Credit</option>
                    <option value="bank transfer" <?php echo ($payment['payment_method'] == 'bank transfer') ? 'selected' : ''; ?>>Bank Transfer</option>
                </select>
            </div>

            <div class="form-group">
                <label for="period_covered">Period Covered:</label>
                <input type="text" id="period_covered" name="period_covered" 
                       value="<?php echo htmlspecialchars($payment['period_covered']); ?>" required>
            </div>

            <div class="form-group">
                <button type="submit">Update Payment</button>
                <a href="payment_list.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>