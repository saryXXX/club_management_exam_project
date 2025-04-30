<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

try {
    $stmt = $pdo->query("SELECT * FROM members ORDER BY last_name, first_name");
    $members = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching members: " . $e->getMessage());
}
?>

<h2>Members</h2>
<a href="create.php" class="btn btn-primary mb-3">Add New Member</a>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Join Date</th>
            <th>Membership</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($members as $member): ?>
        <tr>
            <td><?= htmlspecialchars($member['member_id']) ?></td>
            <td><?= htmlspecialchars($member['first_name']) ?> <?= htmlspecialchars($member['last_name']) ?></td>
            <td><?= htmlspecialchars($member['email']) ?></td>
            <td><?= htmlspecialchars($member['phone']) ?></td>
            <td><?= htmlspecialchars($member['join_date']) ?></td>
            <td><?= ucfirst(htmlspecialchars($member['membership_type'])) ?></td>
            <td><?= $member['active'] ? 'Active' : 'Inactive' ?></td>
            <td>
                <a href="view.php?id=<?= $member['member_id'] ?>" class="btn btn-info btn-sm">View</a>
                <a href="edit.php?id=<?= $member['member_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete.php?id=<?= $member['member_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>