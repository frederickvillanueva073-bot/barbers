<?php
require_once 'auth.php';
requireAdmin();

require_once 'db_connect.php';

$error = '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $staff_id = filter_input(INPUT_POST, 'staff_id', FILTER_VALIDATE_INT);
    $admin_password = $_POST['admin_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (!$staff_id || $staff_id <= 0) {
        $error = 'Please select a staff account.';
    } elseif ($admin_password === '') {
        $error = 'Enter your current admin password.';
    } elseif (strlen($new_password) < 8) {
        $error = 'The new password must have at least 8 characters.';
    } elseif ($new_password !== $confirm_password) {
        $error = 'The new passwords do not match.';
    } else {
        // Verify the person resetting the password is the admin.
        $admin_id = $_SESSION['user_id'];

        $stmt = mysqli_prepare(
            $conn,
            "SELECT password
             FROM users
             WHERE user_id = ? AND role = 'admin'"
        );

        mysqli_stmt_bind_param($stmt, 'i', $admin_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $stored_password);

        $validAdmin = false;

        if (mysqli_stmt_fetch($stmt)) {
            if (password_verify($admin_password, (string) $stored_password)) {
                $validAdmin = true;
            }

            if (hash_equals((string) $stored_password, $admin_password)) {
                $validAdmin = true;
            }
        }

        mysqli_stmt_close($stmt);

        if (!$validAdmin) {
            $error = 'Your current admin password is incorrect.';
        } else {
            // Store the new password as a hash.
            $saved_password = password_hash($new_password, PASSWORD_DEFAULT);

            $stmt = mysqli_prepare(
                $conn,
                "UPDATE users
                 SET password = ?
                 WHERE user_id = ? AND role = 'staff'"
            );

            mysqli_stmt_bind_param(
                $stmt,
                'si',
                $saved_password,
                $staff_id
            );

            if (mysqli_stmt_execute($stmt)) {
                if (mysqli_stmt_affected_rows($stmt) === 1) {
                    $message = 'Staff password reset successfully.';
                } else {
                    $error = 'Staff account not found.';
                }
            } else {
                $error = 'Unable to reset the password.';
            }

            mysqli_stmt_close($stmt);
        }
    }
}

$staffAccounts = mysqli_query(
    $conn,
    "SELECT user_id, username
     FROM users
     WHERE role = 'staff'
     ORDER BY username"
);

if (!$staffAccounts) {
    exit('Unable to load staff accounts.');
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Staff Password - Baldbershop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <h2>Reset Staff Password</h2>

            <p>Confirm the staff member's identity before resetting their password.</p>

            <p class="error">
                <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <p class="success">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </p>

            <form action="reset_password.php" method="POST">
                <label for="staff_id">Staff Account</label>
                <select name="staff_id" id="staff_id" required>
                    <option value="">-- Select staff account --</option>

                    <?php while ($staff = mysqli_fetch_assoc($staffAccounts)) { ?>
                        <option value="<?php echo (int) $staff['user_id']; ?>">
                            <?php
                            echo htmlspecialchars(
                                $staff['username'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="admin_password">Your Current Admin Password</label>
                <input
                    type="password"
                    name="admin_password"
                    id="admin_password"
                    autocomplete="current-password"
                    required>

                <label for="new_password">New Staff Password</label>
                <input
                    type="password"
                    name="new_password"
                    id="new_password"
                    autocomplete="new-password"
                    minlength="8"
                    required>

                <label for="confirm_password">Confirm New Staff Password</label>
                <input
                    type="password"
                    name="confirm_password"
                    id="confirm_password"
                    autocomplete="new-password"
                    minlength="8"
                    required>

                <button type="submit">Reset Password</button>
                <a href="home.php">Back to Home</a>
            </form>
        </div>
    </div>
</body>

</html>