<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id']) && $_SESSION['role_id'] == 2) {
    $caregiver_id = $_SESSION['user_id'];
    $sql = "INSERT INTO caregiver_applications (caregiver_id, status) VALUES (?, 'Pending')";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $caregiver_id);
    $stmt->execute();

    echo "<p class='text-success'>Application submitted!</p>";
}
?>

<h2>Caregiver Application</h2>
<form method="POST" enctype="multipart/form-data">
    <div class="mb-3">
        <label for="qualifications" class="form-label">Upload Qualifications</label>
        <input type="file" name="qualifications" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Submit Application</button>
</form>

<?php include 'includes/footer.php'; ?>