<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

// Fetch potential coaches
try {
    $stmt = $pdo->query("SELECT staff_id, first_name, last_name FROM staff ORDER BY last_name, first_name");
    $coaches = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching coaches: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $coach_id = !empty($_POST['coach_id']) ? $_POST['coach_id'] : null;
        
        $stmt = $pdo->prepare("INSERT INTO teams (team_name, sport_type, coach_id, created_at) VALUES (?, ?, ?, ?)");
        $stmt->execute([
            $_POST['team_name'],
            $_POST['sport_type'],
            $coach_id,
            date('Y-m-d')
        ]);
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Error creating team: " . $e->getMessage());
    }
}
?>

<h2>Add New Team</h2>
<form method="post">
    <div class="mb-3">
        <label for="team_name" class="form-label">Team Name</label>
        <input type="text" class="form-control" id="team_name" name="team_name" required>
    </div>
    <div class="mb-3">
        <label for="sport_type" class="form-label">Sport Type</label>
        <input type="text" class="form-control" id="sport_type" name="sport_type" required>
    </div>
    <div class="mb-3">
        <label for="coach_id" class="form-label">Coach (Optional)</label>
        <select class="form-select" id="coach_id" name="coach_id">
            <option value="">-- No Coach --</option>
            <?php foreach ($coaches as $coach): ?>
            <option value="<?= $coach['staff_id'] ?>"><?= htmlspecialchars($coach['first_name']) ?> <?= htmlspecialchars($coach['last_name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>