<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['child_id'])) {
    $child_id = $_POST['child_id'];
    $sql = "INSERT INTO attendance (child_id, check_in_time, date) VALUES (?, NOW(), CURDATE()) 
            ON DUPLICATE KEY UPDATE check_in_time = NOW()";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $child_id);
    $stmt->execute();

    $sql = "SELECT parent_id FROM children WHERE child_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $child_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $parent = $result->fetch_assoc();
    $parent_id = $parent['parent_id'];

    $message = "Child checked in at " . date('h:i A');
    $sql = "INSERT INTO notifications (user_id, message, notification_type, channel) 
            VALUES (?, ?, 'Attendance', 'SMS')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $parent_id, $message);
    $stmt->execute();

    echo "<p class='text-success'>Check-in recorded!</p>";
}

$sql = "SELECT child_id, first_name, last_name FROM children";
$result = $conn->query($sql);
?>

<h2>Attendance Check-In</h2>
<form method="POST">
    <select name="child_id" class="form-select mb-3" required>
        <option value="">Select Child</option>
        <?php while ($row = $result->fetch_assoc()): ?>
            <option value="<?php echo $row['child_id']; ?>">
                <?php echo $row['first_name'] . ' ' . $row['last_name']; ?>
            </option>
        <?php endwhile; ?>
    </select>
    <button type="submit" class="btn btn-primary">Check In</button>
</form>

<?php include 'includes/footer.php'; ?>