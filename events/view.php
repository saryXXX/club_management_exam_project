<?php
session_start();
require_once('../includes/db_connection.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
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

// Fetch event data with organizer and team information
try {
    $stmt = $pdo->prepare("
        SELECT e.*, 
               CONCAT(s.first_name, ' ', s.last_name) as organizer_name,
               t.team_name 
        FROM events e 
        LEFT JOIN staff s ON e.organizer_id = s.staff_id 
        LEFT JOIN teams t ON e.team_id = t.team_id 
        WHERE e.event_id = ?
    ");
    $stmt->execute([$event_id]);
    $event = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        $_SESSION['error'] = "Event not found";
        header('Location: event_list.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching event data: " . $e->getMessage();
    header('Location: event_list.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom Styles -->
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4">Event Details</h2>

        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Event Name:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($event['event_name']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Description:</strong></div>
                    <div class="col-md-8"><?php echo nl2br(htmlspecialchars($event['description'])); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Date & Time:</strong></div>
                    <div class="col-md-8"><?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Location:</strong></div>
                    <div class="col-md-8"><?php echo htmlspecialchars($event['location']); ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Organizer:</strong></div>
                    <div class="col-md-8"><?php echo $event['organizer_name'] ? htmlspecialchars($event['organizer_name']) : 'Not assigned'; ?></div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4"><strong>Team:</strong></div>
                    <div class="col-md-8"><?php echo $event['team_name'] ? htmlspecialchars($event['team_name']) : 'No team assigned'; ?></div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="event_list.php" class="btn btn-secondary">Back to Event List</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="edit.php?id=<?php echo $event['event_id']; ?>" class="btn btn-warning ms-2">Edit</a>
                <a href="delete.php?id=<?php echo $event['event_id']; ?>" class="btn btn-danger ms-2" 
                   onclick="return confirm('Are you sure you want to delete this event?')">Delete</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS & Dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
</body>
</html>
