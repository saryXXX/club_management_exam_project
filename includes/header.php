<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: suppress warnings and notices (dev use only)
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Club Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/club_management/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Light-themed navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-light border-bottom">
        <div class="container">
            <a class="navbar-brand" href="/club_management/dashboard.php">Club Management</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/club_management/members/index.php">Members</a>
                        </li>
                    <?php endif; ?>

                    <?php if ($_SESSION['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/club_management/staff/index.php">Staff</a>
                        </li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link" href="/club_management/teams/index.php">Teams</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/club_management/events/index.php">Events</a>
                    </li>

                    <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="/club_management/equipment/index.php">Equipment</a>
                        </li>
                    <?php endif; ?>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo htmlspecialchars($_SESSION['name'] ?? 'User'); ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/club_management/auth/logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Hide error messages -->
        <?php /* if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; */ ?>

        <!-- Show success messages only -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>
