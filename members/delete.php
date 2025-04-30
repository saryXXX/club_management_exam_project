<?php
require_once '../includes/db_connection.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$member_id = $_GET['id'];

try {
    // First, delete related records in junction table
    $stmt = $pdo->prepare("DELETE FROM member_team WHERE member_id = ?");
    $stmt->execute([$member_id]);
    
    // Then delete the member
    $stmt = $pdo->prepare("DELETE FROM members WHERE member_id = ?");
    $stmt->execute([$member_id]);
    
    header("Location: index.php");
    exit();
} catch (PDOException $e) {
    die("Error deleting member: " . $e->getMessage());
}
?>