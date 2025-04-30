<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO members (first_name, last_name, email, phone, join_date, membership_type, active) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['join_date'],
            $_POST['membership_type'],
            isset($_POST['active']) ? 1 : 0
        ]);
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Error creating member: " . $e->getMessage());
    }
}
?>

<h2>Add New Member</h2>
<form method="post">
    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" required>
    </div>
    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" required>
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" class="form-control" id="phone" name="phone">
    </div>
    <div class="mb-3">
        <label for="join_date" class="form-label">Join Date</label>
        <input type="date" class="form-control" id="join_date" name="join_date" required>
    </div>
    <div class="mb-3">
        <label for="membership_type" class="form-label">Membership Type</label>
        <select class="form-select" id="membership_type" name="membership_type" required>
            <option value="regular">Regular</option>
            <option value="premium">Premium</option>
            <option value="vip">VIP</option>
        </select>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="active" name="active" checked>
        <label class="form-check-label" for="active">Active Member</label>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>