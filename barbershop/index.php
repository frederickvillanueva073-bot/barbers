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
        $error = 'Unable to log in. Please try again.';
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

        $validPassword = false;

        if (mysqli_stmt_fetch($stmt)) {
            // Check passwords saved using password_hash().
           if (password_verify($password, (string) $dbPassword)) {
                $validPassword = true;
            }

            // Keep existing plain-text accounts working.
            if (hash_equals((string) $dbPassword, $password)) {
                $validPassword = true;
            }
        }

        mysqli_stmt_close($stmt);

        if ($validPassword) {
            session_regenerate_id(true);

            $_SESSION['user_id'] = (int) $userId;
            $_SESSION['username'] = $dbUsername;
            $_SESSION['role'] = $dbRole;

            header('Location: home.php');
            exit();
        }

        $error = 'Invalid Username or Password';
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

    <style>
        .show-password {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 16px;
        }

        .show-password input {
            width: auto;
            margin: 0;
        }

        .forgot-password {
            text-align: center;
            color: #51245f;
        }
    </style>
</head>

<body>
    <div class="left">
        <h1>Baldbershop ✄</h1>
        <p>Queue &amp; Appointment System</p>
    </div>

    <div class="right">
        <form method="POST" action="">
            <input
                type="text"
                name="username"
                placeholder="Username"
                autocomplete="username"
                required>

            <input
                type="password"
                name="password"
                id="password"
                placeholder="Password"
                autocomplete="current-password"
                required>

            <label class="show-password">
                <input
                    type="checkbox"
                    id="showPassword"
                    onchange="togglePassword()">
                Show password
            </label>

            <p id="error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <button type="submit">LOGIN</button>

            <a
                href="#"
                class="forgot-password"
                onclick="forgotPassword(); return false;">
                Forgot Password?
            </a>
        </form>
    </div>

    <script>
        function togglePassword() {
            var password = document.getElementById("password");
            var checkbox = document.getElementById("showPassword");

            if (checkbox.checked) {
                password.type = "text";
            } else {
                password.type = "password";
            }
        }

        function forgotPassword() {
            alert("Please contact your administrator to reset your staff password.");
        }
    </script>
</body>

</html>