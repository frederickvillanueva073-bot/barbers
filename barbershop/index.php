<?php
session_start();
include "db_connect.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "
        SELECT * FROM users
        WHERE username='$username'
        AND password='$password'
    ");

    if (mysqli_num_rows($query) > 0) {

        $_SESSION['username'] = $username;

        header("Location: home.php");
        exit();

    } else {

        $error = "Invalid Username or Password";

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
            <p class="demo">Demo account: admin / admin123</p>
        </form>
    </div>

</body>

</html>
