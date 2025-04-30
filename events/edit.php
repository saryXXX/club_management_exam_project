<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in and has admin privileges
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit();
}

// Check if ID parameter exists
if (!isset($_GET['id'])) {
    $_SESSION['error'] = "Invalid event ID";
    header('Location: event_list.php');
    exit();
}

$event_id = intval($_GET['id']);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $event_name = trim($_POST['event_name']);
        $description = trim($_POST['description']);
        $event_date = trim($_POST['event_date']);
        $location = trim($_POST['location']);
        $organizer_id = !empty($_POST['organizer_id']) ? intval($_POST['organizer_id']) : null;
        $team_id = !empty($_POST['team_id']) ? intval($_POST['team_id']) : null;

        // Validate inputs
        if (empty($event_name) || empty($event_date) || empty($location)) {
            throw new Exception("Event name, date, and location are required");
        }

        // Update event record
        $stmt = $pdo->prepare("UPDATE events SET event_name = ?, description = ?, event_date = ?, 
                              location = ?, organizer_id = ?, team_id = ? WHERE event_id = ?");
        $stmt->execute([$event_name, $description, $event_date, $location, $organizer_id, $team_id, $event_id]);

        $_SESSION['success'] = "Event updated successfully";
        header('Location: event_list.php');
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = "Error updating event: " . $e->getMessage();
    }
}

// Fetch event data
try {
    $stmt = $pdo->prepare("SELECT * FROM events WHERE event_id = ?");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $_SESSION['error'] = "Event not found";
        header('Location: event_list.php');
        exit();
    }

    // Fetch staff members for organizer dropdown
    $staff_stmt = $pdo->query("SELECT staff_id, first_name, last_name FROM staff");
    $staff_members = $staff_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch teams for team dropdown
    $team_stmt = $pdo->query("SELECT team_id, team_name FROM teams");
    $teams = $team_stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching data: " . $e->getMessage();
    header('Location: event_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Edit Event</h2>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="event_name" class="form-label">Event Name</label>
                <input type="text" class="form-control" id="event_name" name="event_name" 
                       value="<?php echo htmlspecialchars($event['event_name']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="description" class="form-label">Description</label>
                <textarea class="form-control" id="description" name="description" rows="4"><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>

            <div class="mb-3">
                <label for="event_date" class="form-label">Event Date</label>
                <input type="datetime-local" class="form-control" id="event_date" name="event_date" 
                       value="<?php echo date('Y-m-d\TH:i', strtotime($event['event_date'])); ?>" required>
            </div>

            <div class="mb-3">
                <label for="location" class="form-label">Location</label>
                <input type="text" class="form-control" id="location" name="location" 
                       value="<?php echo htmlspecialchars($event['location']); ?>" required>
            </div>

            <div class="mb-3">
                <label for="organizer_id" class="form-label">Organizer</label>
                <select class="form-select" id="organizer_id" name="organizer_id">
                    <option value="">Select an organizer</option>
                    <?php foreach ($staff_members as $staff): ?>
                        <option value="<?php echo $staff['staff_id']; ?>" 
                                <?php echo ($event['organizer_id'] == $staff['staff_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($staff['first_name'] . ' ' . $staff['last_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="team_id" class="form-label">Team</label>
                <select class="form-select" id="team_id" name="team_id">
                    <option value="">Select a team</option>
                    <?php foreach ($teams as $team): ?>
                        <option value="<?php echo $team['team_id']; ?>" 
                                <?php echo ($event['team_id'] == $team['team_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($team['team_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <button type="submit" class="btn btn-primary">Update Event</button>
                <a href="event_list.php" class="btn btn-secondary ms-2">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
