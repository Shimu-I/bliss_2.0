<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role_id = $_SESSION['role_id'];

if ($role_id == 1) {
    $sql = "SELECT c.child_id, c.first_name, c.last_name, a.check_in_time, a.check_out_time 
            FROM children c LEFT JOIN attendance a ON c.child_id = a.child_id AND a.date = CURDATE() 
            WHERE c.parent_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
} else {
    $sql = "SELECT c.child_id, c.first_name, c.last_name, a.check_in_time, a.check_out_time 
            FROM children c LEFT JOIN attendance a ON c.child_id = a.child_id AND a.date = CURDATE()";
}
$stmt = $conn->prepare($sql);
if ($role_id == 1) $stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<h2>Dashboard</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>Child Name</th>
            <th>Check-In Time</th>
            <th>Check-Out Time</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?php echo $row['first_name'] . ' ' . $row['last_name']; ?></td>
                <td><?php echo $row['check_in_time'] ? $row['check_in_time'] : 'N/A'; ?></td>
                <td><?php echo $row['check_out_time'] ? $row['check_out_time'] : 'N/A'; ?></td>
            </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<?php include 'includes/footer.php'; ?>