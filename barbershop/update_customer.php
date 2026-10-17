<?php
include "db_connect.php";

$id = $_POST['customer_id'];
$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];

$sql = "UPDATE customers 
        SET name='$name',
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