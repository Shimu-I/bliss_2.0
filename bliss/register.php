<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $role_id = $_POST['role_id'];
    $password = $_POST['password']; // No hashing, store as plain text

    $sql = "INSERT INTO users (role_id, first_name, last_name, email, phone, password) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssss", $role_id, $first_name, $last_name, $email, $phone, $password);
    $stmt->execute();

    echo "<p class='text-success'>Registration successful!</p>";
}
?>

<h2>Register</h2>
<form method="POST">
    <div class="mb-3">
        <label for="first_name" class="form-label">First Name</label>
        <input type="text" name="first_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="last_name" class="form-label">Last Name</label>
        <input type="text" name="last_name" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="phone" class="form-label">Phone</label>
        <input type="text" name="phone" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="role_id" class="form-label">Role</label>
        <select name="role_id" class="form-control" required>
            <option value="1">Parent</option>
            <option value="2">Caregiver</option>
            <option value="3">Admin</option>
        </select>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Register</button>
</form>

<?php include 'includes/footer.php'; ?>