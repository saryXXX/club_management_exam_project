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
    $_SESSION['error'] = "Invalid team ID";
    header('Location: team_list.php');
    exit();
}

$team_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $team_name = trim($_POST['team_name']);
        $sport_type = trim($_POST['sport_type']);
        $coach_id = !empty($_POST['coach_id']) ? intval($_POST['coach_id']) : null;
        $created_at = trim($_POST['created_at']);

        // Validate inputs
        if (empty($team_name) || empty($sport_type) || empty($created_at)) {
            throw new Exception("Team name, sport type, and creation date are required");
        }

        // Update team record
        $stmt = $pdo->prepare("UPDATE teams SET team_name = ?, sport_type = ?, coach_id = ?, created_at = ? WHERE team_id = ?");
        $stmt->execute([$team_name, $sport_type, $coach_id, $created_at, $team_id]);

        $_SESSION['success'] = "Team information updated successfully";
        header('Location: team_list.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating team: " . $e->getMessage();
    }
}

// Fetch team data
try {
    $stmt = $pdo->prepare("SELECT * FROM teams WHERE team_id = ?");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        $_SESSION['error'] = "Team not found";
        header('Location: team_list.php');
        exit();
    }

    // Fetch all staff members who can be coaches
    $coach_stmt = $pdo->prepare("SELECT staff_id, first_name, last_name FROM staff");
    $coach_stmt->execute();
    $coaches = $coach_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching team data: " . $e->getMessage();
    header('Location: team_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Team</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Edit Team</h1>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="team_name">Team Name:</label>
                <input type="text" id="team_name" name="team_name" 
                       value="<?php echo htmlspecialchars($team['team_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="sport_type">Sport Type:</label>
                <input type="text" id="sport_type" name="sport_type" 
                       value="<?php echo htmlspecialchars($team['sport_type']); ?>" required>
            </div>

            <div class="form-group">
                <label for="coach_id">Coach:</label>
                <select id="coach_id" name="coach_id">
                    <option value="">Select a coach</option>
                    <?php foreach ($coaches as $coach): ?>
                        <option value="<?php echo $coach['staff_id']; ?>" 
                                <?php echo ($team['coach_id'] == $coach['staff_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($coach['first_name'] . ' ' . $coach['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="created_at">Creation Date:</label>
                <input type="date" id="created_at" name="created_at" 
                       value="<?php echo htmlspecialchars($team['created_at']); ?>" required>
            </div>

            <div class="form-group">
                <button type="submit">Update Team</button>
                <a href="team_list.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>