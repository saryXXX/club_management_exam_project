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
    $_SESSION['error'] = "Invalid team ID";
    header('Location: team_list.php');
    exit();
}

$team_id = intval($_GET['id']);

// Fetch team data with coach information
try {
    $stmt = $pdo->prepare("
        SELECT t.*, CONCAT(s.first_name, ' ', s.last_name) as coach_name 
        FROM teams t 
        LEFT JOIN staff s ON t.coach_id = s.staff_id 
        WHERE t.team_id = ?
    ");
    $stmt->execute([$team_id]);
    $team = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$team) {
        $_SESSION['error'] = "Team not found";
        header('Location: team_list.php');
        exit();
    }
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
    <title>View Team</title>
    <link rel="stylesheet" href="../css/style.css">
    <style>
        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            color: #2c3e50;
        }

        .container {
            max-width: 1100px;
            margin: 30px auto;
            padding: 20px;
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 700;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .alert.error {
            background-color: #f8d7da;
            color: #721c24;
        }

        .team-details {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .team-details p {
            font-size: 1.2rem;
            line-height: 1.8;
        }

        .team-details strong {
            font-weight: bold;
        }

        .actions {
            text-align: center;
        }

        .btn, .btn-secondary, .btn-danger {
            padding: 10px 20px;
            margin: 5px;
            display: inline-block;
            border-radius: 5px;
            text-decoration: none;
            transition: background-color 0.3s ease;
        }

        .btn {
            background-color: #007bff;
            color: white;
        }

        .btn:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }

        @media (max-width: 768px) {
            .btn {
                width: 100%;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Team Details</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <div class="team-details">
            <p><strong>Team Name:</strong> <?php echo htmlspecialchars($team['team_name']); ?></p>
            <p><strong>Sport Type:</strong> <?php echo htmlspecialchars($team['sport_type']); ?></p>
            <p><strong>Coach:</strong> <?php echo $team['coach_name'] ? htmlspecialchars($team['coach_name']) : 'No coach assigned'; ?></p>
            <p><strong>Created At:</strong> <?php echo htmlspecialchars($team['created_at']); ?></p>
        </div>

        <div class="actions">
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="edit.php?id=<?php echo $team['team_id']; ?>" class="btn">Edit</a>
                <a href="delete.php?id=<?php echo $team['team_id']; ?>" class="btn-danger" 
                   onclick="return confirm('Are you sure you want to delete this team?')">Delete</a>
            <?php endif; ?>
            <a href="team_list.php" class="btn-secondary">Back to List</a>
        </div>
    </div>
</body>
</html>
