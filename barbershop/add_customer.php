<?php
include "db_connect.php";

$name = $_POST['name'];
$phone = $_POST['phone'];
$email = $_POST['email'];

$sql = "INSERT INTO customers (name, phone, email)
        VALUES ('$name', '$phone', '$email')";

if (mysqli_query($conn, $sql)) {
    header("Location: customer.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>