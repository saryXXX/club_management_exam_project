<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Insert the new event into the database
        $stmt = $pdo->prepare("
            INSERT INTO events (event_name, description, event_date, location, organizer_id, team_id) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $_POST['event_name'],
            $_POST['description'],
            $_POST['event_date'],
            $_POST['location'],
            $_POST['organizer_id'],
            $_POST['team_id']
        ]);
        // Redirect to the event list page after successful insert
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Error creating event: " . $e->getMessage());
    }
}

// Fetch available teams and staff members for dropdowns
$teamsStmt = $pdo->query("SELECT * FROM teams");
$teams = $teamsStmt->fetchAll();

$staffStmt = $pdo->query("SELECT staff_id, CONCAT(first_name, ' ', last_name) AS name FROM staff");
$staff = $staffStmt->fetchAll();
?>

<h2>Add New Event</h2>

<form method="post">
    <div class="mb-3">
        <label for="event_name" class="form-label">Event Name</label>
        <input type="text" class="form-control" id="event_name" name="event_name" required>
    </div>

    <div class="mb-3">
        <label for="description" class="form-label">Event Description</label>
        <textarea class="form-control" id="description" name="description" rows="4" required></textarea>
    </div>

    <div class="mb-3">
        <label for="event_date" class="form-label">Event Date</label>
        <input type="datetime-local" class="form-control" id="event_date" name="event_date" required>
    </div>

    <div class="mb-3">
        <label for="location" class="form-label">Location</label>
        <input type="text" class="form-control" id="location" name="location" required>
    </div>

    <div class="mb-3">
        <label for="team_id" class="form-label">Assigned Team</label>
        <select class="form-select" id="team_id" name="team_id" required>
            <option value="">Select Team</option>
            <?php foreach ($teams as $team): ?>
                <option value="<?= $team['team_id'] ?>"><?= htmlspecialchars($team['team_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="mb-3">
        <label for="organizer_id" class="form-label">Organizer</label>
        <select class="form-select" id="organizer_id" name="organizer_id" required>
            <option value="">Select Organizer</option>
            <?php foreach ($staff as $member): ?>
                <option value="<?= $member['staff_id'] ?>"><?= htmlspecialchars($member['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>
