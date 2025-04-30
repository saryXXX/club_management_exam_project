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

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if payment exists
    $stmt = $pdo->prepare("SELECT * FROM payments WHERE payment_id = ?");
    $stmt->execute([$payment_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Payment record not found");
    }

    // Delete payment record
    $delete_stmt = $pdo->prepare("DELETE FROM payments WHERE payment_id = ?");
    $delete_stmt->execute([$payment_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Payment record deleted successfully";
    header('Location: payment_list.php');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting payment record: " . $e->getMessage();
    header('Location: payment_list.php');
    exit();
}
?>