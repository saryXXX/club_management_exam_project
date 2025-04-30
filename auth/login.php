<?php
session_start();
require_once('../includes/db_connection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Validate inputs
        if (empty($email) || empty($password)) {
            throw new Exception("All fields are required");
        }

        // Get user from database
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];

            // Get additional user details based on role
            if ($user['role'] === 'member') {
                $stmt = $pdo->prepare("SELECT member_id, first_name FROM members WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);
                $member = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['member_id'] = $member['member_id'];
                $_SESSION['name'] = $member['first_name'];
            } elseif ($user['role'] === 'staff') {
                $stmt = $pdo->prepare("SELECT staff_id, first_name FROM staff WHERE user_id = ?");
                $stmt->execute([$user['user_id']]);
                $staff = $stmt->fetch(PDO::FETCH_ASSOC);
                $_SESSION['staff_id'] = $staff['staff_id'];
                $_SESSION['name'] = $staff['first_name'];
            }

            header('Location: ../dashboard.php');  // Changed from index.php
            exit();
        } else {
            throw new Exception("Invalid email or password");
        }
    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container d-flex justify-content-center align-items-center min-vh-100">
        <div class="auth-container p-4 border rounded shadow-sm" style="width: 100%; max-width: 400px;">
            <h2 class="text-center mb-4">Login</h2>
            
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </div>

                <p class="text-center">
                    Don't have an account? <a href="register.php">Register here</a>
                </p>
            </form>
        </div>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
