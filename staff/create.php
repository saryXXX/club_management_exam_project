<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("INSERT INTO staff (first_name, last_name, email, phone, position, hire_date, salary) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['position'],
            $_POST['hire_date'],
            $_POST['salary']
        ]);
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Error creating staff: " . $e->getMessage());
    }
}
?>

<h2>Add New Staff Member</h2>
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
        <label for="position" class="form-label">Position</label>
        <input type="text" class="form-control" id="position" name="position" required>
    </div>
    <div class="mb-3">
        <label for="hire_date" class="form-label">Hire Date</label>
        <input type="date" class="form-control" id="hire_date" name="hire_date" required>
    </div>
    <div class="mb-3">
        <label for="salary" class="form-label">Salary</label>
        <input type="number" step="0.01" class="form-control" id="salary" name="salary" required>
    </div>
    <button type="submit" class="btn btn-primary">Submit</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>