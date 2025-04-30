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
    $_SESSION['error'] = "Invalid staff ID";
    header('Location: staff_list.php');
    exit();
}

$staff_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $position = trim($_POST['position']);
        $hire_date = trim($_POST['hire_date']);
        $salary = trim($_POST['salary']);

        // Validate inputs
        if (empty($first_name) || empty($last_name) || empty($email) || empty($position) || empty($hire_date) || empty($salary)) {
            throw new Exception("All fields except phone are required");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        if (!is_numeric($salary) || $salary < 0) {
            throw new Exception("Invalid salary amount");
        }

        // Update staff record
        $stmt = $pdo->prepare("UPDATE staff SET first_name = ?, last_name = ?, email = ?, phone = ?, position = ?, hire_date = ?, salary = ? WHERE staff_id = ?");
        $stmt->execute([$first_name, $last_name, $email, $phone, $position, $hire_date, $salary, $staff_id]);

        $_SESSION['success'] = "Staff information updated successfully";
        header('Location: staff_list.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating staff: " . $e->getMessage();
    }
}

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
    <title>Edit Staff Member</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Staff Member</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($staff['first_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($staff['last_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($staff['email']); ?>" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($staff['phone']); ?>">
            </div>

            <div class="form-group">
                <label for="position">Position:</label>
                <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($staff['position']); ?>" required>
            </div>

            <div class="form-group">
                <label for="hire_date">Hire Date:</label>
                <input type="date" id="hire_date" name="hire_date" value="<?php echo htmlspecialchars($staff['hire_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="salary">Salary:</label>
                <input type="number" id="salary" name="salary" step="0.01" min="0" value="<?php echo htmlspecialchars($staff['salary']); ?>" required>
            </div>

            <div class="form-group">
                <button type="submit">Update Staff</button>
                <a href="staff_list.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>