
<?php
require_once 'auth.php';
require_once 'db_connect.php';

$error = '';

if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = mysqli_prepare(
        $conn,
        'SELECT user_id, username, password, role
         FROM users
         WHERE username = ?
         LIMIT 1'
    );

    if (!$stmt) {
        error_log('Login query failed: ' . mysqli_error($conn));
        $error = 'A database error occurred. Check the PHP error log.';
    } else {
        mysqli_stmt_bind_param($stmt, 's', $username);
        mysqli_stmt_execute($stmt);

        mysqli_stmt_bind_result(
            $stmt,
            $userId,
            $dbUsername,
            $dbPassword,
            $dbRole
        );

        if (
            mysqli_stmt_fetch($stmt) &&
            hash_equals((string) $dbPassword, (string) $password)
        ) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $userId;
            $_SESSION['username'] = $dbUsername;
            $_SESSION['role'] = $dbRole;

            mysqli_stmt_close($stmt);

            header('Location: home.php');
            exit();
        }

        $error = 'Invalid Username or Password';
        mysqli_stmt_close($stmt);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Baldbershop</title>
    <link rel="stylesheet" href="login.css">
</head>

<body>

    <div class="left">
        <h1>Baldbershop ✄</h1>
        <p>Queue &amp; Appointment System</p>
    </div>

    <div class="right">
        <form method="POST" action="">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <p id="error"><?php echo $error; ?></p>
            <button type="submit">LOGIN</button>
            <p class="demo">Admin: admin / admin123 <br> Staff: staff / staff123</p>
        </form>
    </div>

</body>

</html>