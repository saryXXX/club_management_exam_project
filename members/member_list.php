<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

// Fetch all members
try {
    $stmt = $pdo->query("SELECT * FROM members ORDER BY last_name, first_name");
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    $_SESSION['error'] = "Error fetching members: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Members List</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container">
        <h1>Members List</h1>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
        <?php endif; ?>

        <?php if ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'staff'): ?>
            <div class="actions">
                <a href="create.php" class="btn">Add New Member</a>
            </div>
        <?php endif; ?>

        <table class="data-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Join Date</th>
                    <th>Membership Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($members)): ?>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($member['email']); ?></td>
                            <td><?php echo htmlspecialchars($member['phone']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($member['join_date'])); ?></td>
                            <td><?php echo ucfirst(htmlspecialchars($member['membership_type'])); ?></td>
                            <td>
                                <a href="view.php?id=<?php echo $member['member_id']; ?>" class="btn-small">View</a>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <a href="edit.php?id=<?php echo $member['member_id']; ?>" class="btn-small">Edit</a>
                                    <a href="delete.php?id=<?php echo $member['member_id']; ?>" 
                                       class="btn-small btn-danger"
                                       onclick="return confirm('Are you sure you want to delete this member?')">Delete</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6">No members found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>