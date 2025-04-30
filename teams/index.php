<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

try {
    $stmt = $pdo->query("
        SELECT t.*, s.first_name as coach_first_name, s.last_name as coach_last_name 
        FROM teams t
        LEFT JOIN staff s ON t.coach_id = s.staff_id
        ORDER BY t.team_name
    ");
    $teams = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching teams: " . $e->getMessage());
}
?>

<h2>Teams</h2>
<a href="create.php" class="btn btn-primary mb-3">Add New Team</a>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>ID</th>
            <th>Team Name</th>
            <th>Sport Type</th>
            <th>Coach</th>
            <th>Created</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($teams as $team): ?>
        <tr>
            <td><?= htmlspecialchars($team['team_id']) ?></td>
            <td><?= htmlspecialchars($team['team_name']) ?></td>
            <td><?= htmlspecialchars($team['sport_type']) ?></td>
            <td>
                <?php if ($team['coach_id']): ?>
                    <?= htmlspecialchars($team['coach_first_name']) ?> <?= htmlspecialchars($team['coach_last_name']) ?>
                <?php else: ?>
                    Not assigned
                <?php endif; ?>
            </td>
            <td><?= htmlspecialchars($team['created_at']) ?></td>
            <td>
                <a href="view.php?id=<?= $team['team_id'] ?>" class="btn btn-info btn-sm">View</a>
                <a href="edit.php?id=<?= $team['team_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete.php?id=<?= $team['team_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>