<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

try {
    $stmt = $pdo->query("
        SELECT e.*, t.team_name, s.first_name as organizer_first, s.last_name as organizer_last
        FROM events e
        LEFT JOIN teams t ON e.team_id = t.team_id
        LEFT JOIN staff s ON e.organizer_id = s.staff_id
        ORDER BY e.event_date DESC
    ");
    $events = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching events: " . $e->getMessage());
}
?>

<h2>Events</h2>
<a href="create.php" class="btn btn-primary mb-3">Add New Event</a>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Event Name</th>
            <th>Date & Time</th>
            <th>Location</th>
            <th>Team</th>
            <th>Organizer</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($events as $event): ?>
        <tr>
            <td><?= htmlspecialchars($event['event_name']) ?></td>
            <td><?= date('M j, Y g:i A', strtotime($event['event_date'])) ?></td>
            <td><?= htmlspecialchars($event['location']) ?></td>
            <td><?= $event['team_name'] ? htmlspecialchars($event['team_name']) : 'General' ?></td>
            <td>
                <?php if ($event['organizer_id']): ?>
                    <?= htmlspecialchars($event['organizer_first']) ?> <?= htmlspecialchars($event['organizer_last']) ?>
                <?php else: ?>
                    Not assigned
                <?php endif; ?>
            </td>
            <td>
                <a href="view.php?id=<?= $event['event_id'] ?>" class="btn btn-info btn-sm">View</a>
                <a href="edit.php?id=<?= $event['event_id'] ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="delete.php?id=<?= $event['event_id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once '../includes/footer.php'; ?>