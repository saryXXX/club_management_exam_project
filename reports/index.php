<?php
require_once '../includes/db_connection.php';
require_once '../includes/header.php';

// Overdue members query
try {
    $stmt = $pdo->query("
        SELECT m.member_id, m.first_name, m.last_name, m.email, 
               MAX(p.payment_date) as last_payment_date
        FROM members m
        LEFT JOIN payments p ON m.member_id = p.member_id
        WHERE m.active = 1
        GROUP BY m.member_id
        HAVING last_payment_date IS NULL OR last_payment_date < DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    ");
    $overdueMembers = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching overdue members: " . $e->getMessage());
}

// Equipment report query
try {
    $stmt = $pdo->query("
        SELECT 
            type,
            COUNT(*) as total,
            SUM(CASE WHEN condition_status = 'excellent' THEN 1 ELSE 0 END) as excellent,
            SUM(CASE WHEN condition_status = 'good' THEN 1 ELSE 0 END) as good,
            SUM(CASE WHEN condition_status = 'fair' THEN 1 ELSE 0 END) as fair,
            SUM(CASE WHEN condition_status = 'poor' THEN 1 ELSE 0 END) as poor,
            SUM(CASE WHEN condition_status = 'broken' THEN 1 ELSE 0 END) as broken
        FROM equipment
        GROUP BY type
    ");
    $equipmentReport = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching equipment report: " . $e->getMessage());
}

// Event participation query
try {
    $stmt = $pdo->query("
        SELECT 
            e.event_id,
            e.event_name,
            e.event_date,
            COUNT(DISTINCT mt.member_id) as participants_count,
            t.team_name
        FROM events e
        LEFT JOIN teams t ON e.team_id = t.team_id
        LEFT JOIN member_team mt ON t.team_id = mt.team_id
        GROUP BY e.event_id
        ORDER BY e.event_date DESC
    ");
    $eventParticipation = $stmt->fetchAll();
} catch (PDOException $e) {
    die("Error fetching event participation: " . $e->getMessage());
}
?>

<h2>Club Reports</h2>

<div class="row mb-4">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-warning">
                <h4>Members with Overdue Payments</h4>
            </div>
            <div class="card-body">
                <?php if (count($overdueMembers) > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Last Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($overdueMembers as $member): ?>
                            <tr>
                                <td><?= htmlspecialchars($member['first_name']) ?> <?= htmlspecialchars($member['last_name']) ?></td>
                                <td><?= htmlspecialchars($member['email']) ?></td>
                                <td><?= $member['last_payment_date'] ? htmlspecialchars($member['last_payment_date']) : 'Never' ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No members with overdue payments found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card">
            <div class="card-header bg-info">
                <h4>Event Participation</h4>
            </div>
            <div class="card-body">
                <?php if (count($eventParticipation) > 0): ?>
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Team</th>
                                <th>Participants</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($eventParticipation as $event): ?>
                            <tr>
                                <td><?= htmlspecialchars($event['event_name']) ?></td>
                                <td><?= htmlspecialchars($event['event_date']) ?></td>
                                <td><?= $event['team_name'] ? htmlspecialchars($event['team_name']) : 'General' ?></td>
                                <td><?= $event['participants_count'] ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p>No event participation data found.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-primary text-white">
        <h4>Equipment Status Report</h4>
    </div>
    <div class="card-body">
        <?php if (count($equipmentReport) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Equipment Type</th>
                        <th>Total</th>
                        <th>Excellent</th>
                        <th>Good</th>
                        <th>Fair</th>
                        <th>Poor</th>
                        <th>Broken</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($equipmentReport as $equip): ?>
                    <tr>
                        <td><?= ucfirst(htmlspecialchars($equip['type'])) ?></td>
                        <td><?= $equip['total'] ?></td>
                        <td><?= $equip['excellent'] ?></td>
                        <td><?= $equip['good'] ?></td>
                        <td><?= $equip['fair'] ?></td>
                        <td><?= $equip['poor'] ?></td>
                        <td><?= $equip['broken'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No equipment data found.</p>
        <?php endif; ?>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>