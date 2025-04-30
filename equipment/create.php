<?php
session_start();
require_once('../includes/db_connection.php');

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $name = trim($_POST['name']);
        $type = trim($_POST['type']);
        $purchase_date = trim($_POST['purchase_date']);
        $condition_status = trim($_POST['condition_status']);
        $assigned_to = !empty($_POST['assigned_to']) ? intval($_POST['assigned_to']) : null;

        if (empty($name) || empty($type) || empty($purchase_date) || empty($condition_status)) {
            throw new Exception("Name, type, purchase date, and condition are required");
        }

        $valid_conditions = ['excellent', 'good', 'fair', 'poor', 'broken'];
        if (!in_array($condition_status, $valid_conditions)) {
            throw new Exception("Invalid condition status");
        }

        // Insert the equipment into the database
        $stmt = $pdo->prepare("INSERT INTO equipment (name, type, purchase_date, condition_status, assigned_to) 
                              VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $type, $purchase_date, $condition_status, $assigned_to]);

        // Set success message
        $_SESSION['success'] = "Equipment added successfully";
        
        // Redirect to index.php after the form submission is successful
        header('Location: index.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error adding equipment: " . $e->getMessage();
    }
}

try {
    $team_stmt = $pdo->query("SELECT team_id, team_name FROM teams");
    $teams = $team_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching teams: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Equipment</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
            color: #2c3e50;
        }

        .container {
            max-width: 700px;
            margin: 40px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        h1 {
            text-align: center;
            margin-bottom: 30px;
            color: #007bff;
        }

        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            font-weight: bold;
            display: block;
            margin-bottom: 8px;
        }

        input[type="text"],
        input[type="date"],
        select {
            width: 100%;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1rem;
        }

        button {
            background-color: #28a745;
            color: white;
            padding: 12px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button:hover {
            background-color: #218838;
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
            padding: 12px 20px;
            font-size: 1rem;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            margin-left: 10px;
            display: inline-block;
            transition: background-color 0.3s ease;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        @media (max-width: 600px) {
            .container {
                margin: 20px;
                padding: 20px;
            }

            button, .btn-secondary {
                width: 100%;
                margin-top: 10px;
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Add New Equipment</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="name">Equipment Name:</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="type">Type:</label>
                <input type="text" id="type" name="type" required>
            </div>

            <div class="form-group">
                <label for="purchase_date">Purchase Date:</label>
                <input type="date" id="purchase_date" name="purchase_date" required>
            </div>

            <div class="form-group">
                <label for="condition_status">Condition:</label>
                <select id="condition_status" name="condition_status" required>
                    <option value="">Select condition</option>
                    <option value="excellent">Excellent</option>
                    <option value="good">Good</option>
                    <option value="fair">Fair</option>
                    <option value="poor">Poor</option>
                    <option value="broken">Broken</option>
                </select>
            </div>

            <div class="form-group">
                <label for="assigned_to">Assign to Team:</label>
                <select id="assigned_to" name="assigned_to">
                    <option value="">Select team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team['team_id']; ?>">
                            <?php echo htmlspecialchars($team['team_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <button type="submit">Add Equipment</button>
                <a href="index.php" class="btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</body>
</html>
