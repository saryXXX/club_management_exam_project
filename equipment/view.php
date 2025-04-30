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
    $_SESSION['error'] = "Invalid equipment ID";
    header('Location: equipment_list.php');
    exit();
}

$equipment_id = intval($_GET['id']);

// Fetch equipment data with team information
try {
    $stmt = $pdo->prepare("
        SELECT e.*, t.team_name 
        FROM equipment e 
        LEFT JOIN teams t ON e.assigned_to = t.team_id 
        WHERE e.equipment_id = ?
    ");
    $stmt->execute([$equipment_id]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipment) {
        $_SESSION['error'] = "Equipment not found";
        header('Location: equipment_list.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching equipment data: " . $e->getMessage();
    header('Location: equipment_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Equipment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Equipment Details</h1>

        <div class="equipment-details">
            <p><strong>Name:</strong> <?php echo htmlspecialchars($equipment['name']); ?></p>
            <p><strong>Type:</strong> <?php echo htmlspecialchars($equipment['type']); ?></p>
            <p><strong>Purchase Date:</strong> <?php echo date('F j, Y', strtotime($equipment['purchase_date'])); ?></p>
            <p><strong>Condition:</strong> 
                <span class="condition-badge <?php echo $equipment['condition_status']; ?>">
                    <?php echo ucfirst(htmlspecialchars($equipment['condition_status'])); ?>
                </span>
            </p>
            <p><strong>Assigned To:</strong> 
                <?php echo $equipment['team_name'] ? htmlspecialchars($equipment['team_name']) : 'Not assigned'; ?>
            </p>
        </div>

        <div class="actions">
            <?php if ($_SESSION['role'] === 'admin'): ?>
            <a href="edit.php?id=<?php echo $equipment['equipment_id']; ?>" class="btn">Edit</a>
            <a href="delete.php?id=<?php echo $equipment['equipment_id']; ?>" class="btn-danger" 
               onclick="return confirm('Are you sure you want to delete this equipment?')">Delete</a>
            <?php endif; ?>
            <a href="equipment_list.php" class="btn-secondary">Back to List</a>
        </div>
    </div>
</body>
</html>