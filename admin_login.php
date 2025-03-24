<?php
session_start();
include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // MD5 encryption for matching

    $sql = "SELECT user_id, role FROM users WHERE email = ? AND password = ? AND role = 'admin'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($user_id, $role);
        $stmt->fetch();
        $_SESSION['user_id'] = $user_id;
        $_SESSION['role'] = $role;
        header("Location: admin_dashboard.php"); // Redirect to Admin Dashboard
        exit();
    } else {
        $error = "Invalid Admin Credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" />
</head>
<body class="bg-light d-flex justify-content-center align-items-center min-vh-100">

    <div class="card shadow-lg rounded p-4" style="width: 400px;">
        <h2 class="text-center mb-4 text-gray">Admin Login</h2>

        <form method="post">
            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" name="email" id="email" class="form-control" required />
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password:</label>
                <input type="password" name="password" id="password" class="form-control" required />
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>

        <?php if (isset($error)) echo "<p class='text-danger text-center mt-3'>$error</p>"; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
