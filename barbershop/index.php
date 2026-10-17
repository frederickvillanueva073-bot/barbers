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
    <title>Login - Barbershop Queue System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container login-box">

    <div class="card">

        <h1>Barbershop Login</h1>

        <p>Queue & Appointment System</p>


        <form method="POST">

            <label>Username</label>

            <input 
                type="text" 
                name="username"
                placeholder="Enter username"
                required
            >


            <label>Password</label>

            <input 
                type="password" 
                name="password"
                placeholder="Enter password"
                required
            >


            <p class="error">
                <?php echo $error; ?>
            </p>


            <button type="submit">
                Login
            </button>


        </form>


        <p>
            <small>
                Demo account: admin / admin123
            </small>
        </p>


    </div>

</div>


</body>

</html>