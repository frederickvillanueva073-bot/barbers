<?php
include "db_connect.php";

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM customers WHERE customer_id=$id");

$customer = mysqli_fetch_assoc($result);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
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

            <label>Full Name</label>
            <input type="text" 
            name="name" 
            value="<?php echo $customer['name']; ?>">

            <label>Phone Number</label>
            <input type="text" 
            name="phone" 
            value="<?php echo $customer['phone']; ?>">

            <label>Email</label>
            <input type="text" 
            name="email" 
            value="<?php echo $customer['email']; ?>">


            <button type="submit">
                Update Customer
            </button>

        </form>

    </div>

</div>

</body>

</html>