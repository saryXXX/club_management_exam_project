<?php
session_start();
require_once('../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Begin transaction for atomic operations
        $pdo->beginTransaction();

        // Sanitize and validate input data
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $phone = trim($_POST['phone']);
        $role = 'member'; // Default role for registration
        $membership_type = 'regular'; // Default membership type

        // Basic validation
        if (empty($email) || empty($password) || empty($confirm_password) || empty($first_name) || empty($last_name)) {
            throw new Exception("All fields are required.");
        }

        // Check if password and confirm password match
        if ($password !== $confirm_password) {
            throw new Exception("Passwords do not match.");
        }

        // Check if email already exists in the database
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("Email is already registered.");
        }

        // Hash the password before storing it
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert user into the users table
        $stmt = $pdo->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, ?)");
        $stmt->execute([$email, $password_hash, $role]);

        // Get the user ID of the newly inserted user
        $user_id = $pdo->lastInsertId();

        // Insert member profile information into the members table
        $stmt = $pdo->prepare("INSERT INTO members (first_name, last_name, email, phone, join_date, membership_type, user_id) 
                               VALUES (?, ?, ?, ?, CURDATE(), ?, ?)");
        $stmt->execute([$first_name, $last_name, $email, $phone, $membership_type, $user_id]);

        // Commit the transaction
        $pdo->commit();

        // Set success message and redirect to login page
        $_SESSION['success'] = "Registration successful. Please login.";
        header('Location: login.php');
        exit();

    } catch (Exception $e) {
        // Rollback the transaction if an error occurs
        $pdo->rollBack();
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <style>
        /* Basic Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background-color: #f4f7fc;
            color: #333;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Container for the form */
        .auth-container {
            background-color: #fff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }

        /* Header */
        .auth-container h1 {
            text-align: center;
            color: #4CAF50;
            margin-bottom: 20px;
        }

        /* Form Group */
        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            font-size: 16px;
            border: 1px solid #ddd;
            border-radius: 4px;
            outline: none;
            box-sizing: border-box;
        }

        .form-group input:focus {
            border-color: #4CAF50;
        }

        /* Error and Success Messages */
        .error, .success {
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .error {
            background-color: #f44336;
            color: white;
        }

        .success {
            background-color: blue;
            color: white;
        }

        /* Submit Button */
        button[type="submit"] {
            width: 100%;
            padding: 12px;
            background-color:#0f5ce0;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        button[type="submit"]:hover {
            background-color: #0f5ce0;
        }

        /* Links for Login */
        .auth-links {
            text-align: center;
            margin-top: 10px;
        }

        .auth-links a {
            color: #0f5ce0;
            text-decoration: none;
        }

        .auth-links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <h1 style="color:#0f5ce0">Register</h1>
        
        <!-- Display error message if there is one -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <!-- Display success message if there is one -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="first_name">First Name:</label>
                <input type="text" id="first_name" name="first_name" required>
            </div>

            <div class="form-group">
                <label for="last_name">Last Name:</label>
                <input type="text" id="last_name" name="last_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="phone">Phone:</label>
                <input type="tel" id="phone" name="phone" required>
            </div>

            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password:</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <button type="submit">Register</button>
            </div>

            <p class="auth-links">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </form>
    </div>
</body>
</html>
