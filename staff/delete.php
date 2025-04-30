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

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if staff exists
    $stmt = $pdo->prepare("SELECT * FROM staff WHERE staff_id = ?");
    $stmt->execute([$staff_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Staff member not found");
    }

    // Delete staff record
    $delete_stmt = $pdo->prepare("DELETE FROM staff WHERE staff_id = ?");
    $delete_stmt->execute([$staff_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Staff member deleted successfully";
    header('Location: staff_list.php');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting staff member: " . $e->getMessage();
    header('Location: staff_list.php');
    exit();
}
?>