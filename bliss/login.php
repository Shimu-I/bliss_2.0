<?php
include 'includes/db_connect.php';
include 'includes/header.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $sql = "SELECT user_id, role_id, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && $password === $user['password']) { // Direct comparison
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['role_id'] = $user['role_id'];
        header("Location: dashboard.php");
        exit;
    } else {
        echo "<p class='text-danger'>Invalid credentials!</p>";
    }
}
?>

<h2>Login</h2>
<form method="POST">
    <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    <div class="mb-3">
        <label for="password" class="form-label">Password</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
</form>

<?php include 'includes/footer.php'; ?>