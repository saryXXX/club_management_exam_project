<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM staff ORDER BY last_name, first_name");
    $staff = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching staff: " . $e->getMessage());
}
?>

<h2>Staff Members</h2>
<a href="create.php" class="btn btn-primary mb-3">Add New Staff</a>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Position</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Hire Date</th>
            <th>Salary</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($staff as $staffMember): ?>
        <tr>
            <td><?= htmlspecialchars($staffMember['staff_id']) ?></td>
            <td><?= htmlspecialchars($staffMember['first_name']) ?> <?= htmlspecialchars($staffMember['last_name']) ?></td>
            <td><?= htmlspecialchars($staffMember['position']) ?></td>
            <td><?= htmlspecialchars($staffMember['email']) ?></td>
            <td><?= htmlspecialchars($staffMember['phone']) ?></td>
            <td><?= htmlspecialchars($staffMember['hire_date']) ?></td>
            <td>$<?= number_format($staffMember['salary'], 2) ?></td>
            <td>
                <a href="view.php?id=<?= $staffMember['staff_id'] ?>" class="btn btn-info btn-sm">View</a>
                <a href="edit.php?id=<?= $staffMember['staff_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete.php?id=<?= $staffMember['staff_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>