
<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id=$id");

$customer = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Customer</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <div class="card">

        <h2>Edit Customer</h2>

        <form action="update_customer.php" method="POST">

            <input type="hidden" name="customer_id"
            value="<?php echo $customer['customer_id']; ?>">

            <label>First Name</label>
            <input type="text"
            name="first_name"
            value="<?php echo $customer['first_name']; ?>" required>

            <label>Middle Name (optional)</label>
            <input type="text"
            name="middle_name"
            value="<?php echo $customer['middle_name']; ?>">

            <label>Last Name</label>
            <input type="text"
            name="last_name"
            value="<?php echo $customer['last_name']; ?>" required>

            <label>Phone Number</label>
            <input type="tel"
            name="phone"
            value="<?php echo $customer['phone']; ?>"
            placeholder="e.g. 09171234567"
            pattern="09[0-9]{9}"
            maxlength="11"
            minlength="11"
            inputmode="numeric"
            title="Enter exactly 11 digits starting with 09."
            required>

            <label>Email (optional)</label>
            <input type="email"
            name="email"
            value="<?php echo $customer['email']; ?>">

            <button type="submit">Update Customer</button>

        </form>

    </div>

</div>

</body>

</html>
