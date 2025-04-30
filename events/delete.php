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
    $_SESSION['error'] = "Invalid event ID";
    header('Location: event_list.php');
    exit();
}

$event_id = intval($_GET['id']);

try {
    // Begin transaction
    $pdo->beginTransaction();

    // Check if event exists
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    
    if ($stmt->rowCount() === 0) {
        throw new Exception("Event not found");
    }

    // Delete event record
    $delete_stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
    $delete_stmt->execute([$event_id]);

    // Commit transaction
    $pdo->commit();

    $_SESSION['success'] = "Event deleted successfully";
    header('Location: event_list.php');
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $pdo->rollBack();
    $_SESSION['error'] = "Error deleting event: " . $e->getMessage();
    header('Location: event_list.php');
    exit();
}
?>