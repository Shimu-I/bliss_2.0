<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];

// Fetch user details
$stmt = $conn->prepare("SELECT first_name, last_name, email FROM Users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Daycare Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2>Welcome, <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>!</h2>
        <p>Role: <?php echo htmlspecialchars($role); ?></p>
        <?php if ($role === 'Parent') { ?>
            <h3>Your Children</h3>
            <?php
            $stmt = $conn->prepare("SELECT child_id, first_name, last_name FROM Children WHERE parent_id = ? AND deleted_at IS NULL");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $children = $stmt->get_result();
            ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($child = $children->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></td>
                            <td><a href="attendance.php?child_id=<?php echo $child['child_id']; ?>" class="btn btn-sm btn-primary">View Attendance</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php $stmt->close(); ?>
        <?php } elseif ($role === 'Caregiver') { ?>
            <h3>Your Assigned Children</h3>
            <?php
            $stmt = $conn->prepare("SELECT c.child_id, c.first_name, c.last_name 
                                    FROM Children c 
                                    JOIN Caregiver_Child_Assignments cca ON c.child_id = cca.child_id 
                                    WHERE cca.caregiver_id = ? AND cca.end_date IS NULL");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $children = $stmt->get_result();
            ?>
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($child = $children->fetch_assoc()) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($child['first_name'] . ' ' . $child['last_name']); ?></td>
                            <td><a href="attendance.php?child_id=<?php echo $child['child_id']; ?>" class="btn btn-sm btn-primary">Manage Attendance</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php $stmt->close(); ?>
        <?php } elseif ($role === 'Admin') { ?>
            <h3>Admin Dashboard</h3>
            <p>Manage users, caregivers, and system settings.</p>
            <a href="attendance.php" class="btn btn-primary">View All Attendance</a>
        <?php } ?>
    </div>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>