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
    $_SESSION['error'] = "Invalid staff ID";
    header('Location: staff_list.php');
    exit();
}

$staff_id = intval($_GET['id']);

// Fetch staff data
try {
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    $staff = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$staff) {
        $_SESSION['error'] = "Staff member not found";
        header('Location: staff_list.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching staff data: " . $e->getMessage();
    header('Location: staff_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Staff Member</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Staff Member Details</h1>

        <div class="staff-details">
            <p><strong>First Name:</strong> <?php echo htmlspecialchars($staff['first_name']); ?></p>
            <p><strong>Last Name:</strong> <?php echo htmlspecialchars($staff['last_name']); ?></p>
            <p><strong>Email:</strong> <?php echo htmlspecialchars($staff['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($staff['phone']); ?></p>
            <p><strong>Position:</strong> <?php echo htmlspecialchars($staff['position']); ?></p>
            <p><strong>Hire Date:</strong> <?php echo htmlspecialchars($staff['hire_date']); ?></p>
            <p><strong>Salary:</strong> $<?php echo number_format($staff['salary'], 2); ?></p>
        </div>

        <div class="actions">
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="edit.php?id=<?php echo $staff['staff_id']; ?>" class="btn">Edit</a>
            <a href="delete.php?id=<?php echo $staff['staff_id']; ?>" class="btn-danger" 
               onclick="return confirm('Are you sure you want to delete this staff member?')">Delete</a>
            <?php endif; ?>
            <a href="index.php" class="btn-secondary">Back to List</a>
        </div>
    </div>
</body>
</html>