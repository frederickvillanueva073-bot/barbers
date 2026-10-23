<?php
include "db_connect.php";

$id = $_POST['customer_id'];
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$last_name = $_POST['last_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];

$sql = "UPDATE customers 
        SET first_name='$first_name',
            middle_name='$middle_name',
            last_name='$last_name',
            phone='$phone',
            email='$email'
        WHERE customer_id=$id";

if (mysqli_query($conn, $sql)) {

    header("Location: customer.php");
    exit();

} else {

    echo "Error updating customer: " . mysqli_error($conn);

}
?>