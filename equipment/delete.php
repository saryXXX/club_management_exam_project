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
    $_SESSION['error'] = "Invalid equipment ID";
    header('Location: equipment_list.php');
    exit();
}

$equipment_id = intval($_GET['id']);

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if equipment exists
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Equipment not found");
    }

    // Delete equipment record
    $delete_stmt = $pdo->prepare("DELETE FROM equipment WHERE equipment_id = ?");
    $delete_stmt->execute([$equipment_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Equipment deleted successfully";
    header('Location: equipment_list.php');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting equipment: " . $e->getMessage();
    header('Location: equipment_list.php');
    exit();
}
?>