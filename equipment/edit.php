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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $purchase_date = trim($_POST['purchase_date']);
        $condition_status = trim($_POST['condition_status']);
        $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;

        // Validate inputs
        if (empty($name) || empty($type) || empty($purchase_date) || empty($condition_status)) {
            throw new Exception("Name, type, purchase date, and condition are required");
        }

        // Validate condition status
        $valid_conditions = ['excellent', 'good', 'fair', 'poor', 'broken'];
        if (!in_array($condition_status, $valid_conditions)) {
            throw new Exception("Invalid condition status");
        }

        // Update equipment record
        $stmt = $pdo->prepare("UPDATE equipment SET name = ?, type = ?, purchase_date = ?, 
                              condition_status = ?, assigned_to = ? WHERE equipment_id = ?");
        $stmt->execute([$name, $type, $purchase_date, $condition_status, $assigned_to, $equipment_id]);

        $_SESSION['success'] = "Equipment updated successfully";
        header('Location: equipment_list.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating equipment: " . $e->getMessage();
    }
}

// Fetch equipment data
try {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE equipment_id = ?");
    $stmt->execute([$equipment_id]);
    $equipment = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$equipment) {
        $_SESSION['error'] = "Equipment not found";
        header('Location: equipment_list.php');
        exit();
    }

    // Fetch teams for dropdown
    $team_stmt = $pdo->query("SELECT team_id, team_name FROM teams");
    $teams = $team_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching data: " . $e->getMessage();
    header('Location: equipment_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Equipment</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Equipment</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Equipment Name:</label>
                <input type="text" id="name" name="name" 
                       value="<?php echo htmlspecialchars($equipment['name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="type">Type:</label>
                <input type="text" id="type" name="type" 
                       value="<?php echo htmlspecialchars($equipment['type']); ?>" required>
            </div>

            <div class="form-group">
                <label for="purchase_date">Purchase Date:</label>
                <input type="date" id="purchase_date" name="purchase_date" 
                       value="<?php echo htmlspecialchars($equipment['purchase_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="condition_status">Condition:</label>
                <select id="condition_status" name="condition_status" required>
                    <option value="">Select condition</option>
                    <?php foreach(['excellent', 'good', 'fair', 'poor', 'broken'] as $condition): ?>
                        <option value="<?php echo $condition; ?>" 
                                <?php echo ($equipment['condition_status'] == $condition) ? 'selected' : ''; ?>>
                            <?php echo ucfirst($condition); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="assigned_to">Assign to Team:</label>
                <select id="assigned_to" name="assigned_to">
                    <option value="">Select team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team['team_id']; ?>" 
                                <?php echo ($equipment['assigned_to'] == $team['team_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($team['team_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <button type="submit">Update Equipment</button>
                <a href="equipment_list.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>