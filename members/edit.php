<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$member_id = $_GET['id'];

try {
    // Fetch member data
    $stmt = $pdo->prepare("SELECT * FROM members WHERE member_id = ?");
    $stmt->execute([$member_id]);
    $member = $stmt->fetch();
    
    if (!$member) {
        header("Location: index.php");
        exit();
    }
} catch (PDOException $e) {
    die("Error fetching member: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE members SET first_name = ?, last_name = ?, email = ?, phone = ?, join_date = ?, membership_type = ?, active = ? WHERE member_id = ?");
        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['email'],
            $_POST['phone'],
            $_POST['join_date'],
            $_POST['membership_type'],
            isset($_POST['active']) ? 1 : 0,
            $member_id
        ]);
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        die("Error updating member: " . $e->getMessage());
    }
}
?>

<h2>Edit Member</h2>
<form method="post">
    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($member['first_name']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($member['last_name']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($member['email']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($member['phone']) ?>">
    </div>
    <div class="mb-3">
        <label for="join_date" class="form-label">Join Date</label>
        <input type="date" class="form-control" id="join_date" name="join_date" value="<?= htmlspecialchars($member['join_date']) ?>" required>
    </div>
    <div class="mb-3">
        <label for="membership_type" class="form-label">Membership Type</label>
        <select class="form-select" id="membership_type" name="membership_type" required>
            <option value="regular" <?= $member['membership_type'] === 'regular' ? 'selected' : '' ?>>Regular</option>
            <option value="premium" <?= $member['membership_type'] === 'premium' ? 'selected' : '' ?>>Premium</option>
            <option value="vip" <?= $member['membership_type'] === 'vip' ? 'selected' : '' ?>>VIP</option>
        </select>
    </div>
    <div class="mb-3 form-check">
        <input type="checkbox" class="form-check-input" id="active" name="active" <?= $member['active'] ? 'checked' : '' ?>>
        <label class="form-check-label" for="active">Active Member</label>
    </div>
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="index.php" class="btn btn-secondary">Cancel</a>
</form>

<?php require_once '../includes/footer.php'; ?>