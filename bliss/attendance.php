<?php
session_start();
require_once 'includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$role = $_SESSION['role'];
$child_id = isset($_GET['child_id']) ? (int)$_GET['child_id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($role === 'Caregiver' || $role === 'Admin')) {
    $child_id = $_POST['child_id'];
    $action = $_POST['action'];
    $current_time = date('Y-m-d H:i:s');
    $current_date = date('Y-m-d');

    $stmt = $conn->prepare("SELECT attendance_id, check_in_time, check_out_time 
                            FROM Attendance 
                            WHERE child_id = ? AND date = ?");
    $stmt->bind_param("is", $child_id, $current_date);
    $stmt->execute();
    $result = $stmt->get_result();
    $attendance = $result->fetch_assoc();

    if ($action === 'check_in' && !$attendance) {
        $stmt = $conn->prepare("INSERT INTO Attendance (child_id, check_in_time, date) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $child_id, $current_time, $current_date);
        $stmt->execute();
    } elseif ($action === 'check_out' && $attendance && !$attendance['check_out_time']) {
        $stmt = $conn->prepare("UPDATE Attendance SET check_out_time = ? WHERE attendance_id = ?");
        $stmt->bind_param("si", $current_time, $attendance['attendance_id']);
        $stmt->execute();
    }
    $stmt->close();
}

// Fetch attendance records
$where_clause = $role === 'Parent' ? "WHERE a.child_id IN (SELECT child_id FROM Children WHERE parent_id = ?)" : "";
$params = $role === 'Parent' ? [$_SESSION['user_id']] : [];
if ($child_id && $role !== 'Admin') {
    $where_clause .= ($where_clause ? " AND" : "WHERE") . " a.child_id = ?";
    $params[] = $child_id;
}

$stmt = $conn->prepare("SELECT a.attendance_id, a.child_id, c.first_name, c.last_name, a.check_in_time, a.check_out_time, a.date 
                        FROM Attendance a 
                        JOIN Children c ON a.child_id = c.child_id 
                        $where_clause 
                        ORDER BY a.date DESC 
                        LIMIT 10");
if ($params) {
    $stmt->bind_param(str_repeat("i", count($params)), ...$params);
}
$stmt->execute();
$attendances = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance - Daycare Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <div class="container mt-5">
        <h2>Attendance Management</h2>
        <?php if ($role === 'Caregiver' || $role === 'Admin') { ?>
            <form method="POST" class="mb-4">
                <div class="row">
                    <div class="col-md-4">
                        <select name="child_id" class="form-select" required>
                            <option value="">Select Child</option>
                            <?php
                            $query = $role === 'Caregiver' 
                                ? "SELECT c.child_id, c.first_name, c.last_name 
                                   FROM Children c 
                                   JOIN Caregiver_Child_Assignments cca ON c.child_id = cca.child_id 
                                   WHERE cca.caregiver_id = ? AND cca.end_date IS NULL"
                                : "SELECT child_id, first_name, last_name FROM Children WHERE deleted_at IS NULL";
                            $stmt = $conn->prepare($query);
                            if ($role === 'Caregiver') {
                                $stmt->bind_param("i", $_SESSION['user_id']);
                            }
                            $stmt->execute();
                            $children = $stmt->get_result();
                            while ($child = $children->fetch_assoc()) {
                                echo "<option value='{$child['child_id']}'>" . htmlspecialchars($child['first_name'] . ' ' . $child['last_name']) . "</option>";
                            }
                            $stmt->close();
                            ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <button type="submit" name="action" value="check_in" class="btn btn-success">Check In</button>
                        <button type="submit" name="action" value="check_out" class="btn btn-warning">Check Out</button>
                    </div>
                </div>
            </form>
        <?php } ?>
        <h3>Attendance Records</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Child</th>
                    <th>Date</th>
                    <th>Check-In</th>
                    <th>Check-Out</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($attendance = $attendances->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($attendance['first_name'] . ' ' . $attendance['last_name']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['date']); ?></td>
                        <td><?php echo htmlspecialchars($attendance['check_in_time'] ?: 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($attendance['check_out_time'] ?: 'N/A'); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <?php $stmt->close(); ?>
    <?php include 'includes/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>