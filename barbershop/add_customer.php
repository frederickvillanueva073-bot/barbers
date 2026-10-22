<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';

include "db_connect.php";

$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$last_name = $_POST['last_name'];
$phone = $_POST['phone'];
$email = $_POST['email'];

$sql = "INSERT INTO customers (first_name, middle_name, last_name, phone, email)
        VALUES ('$first_name', '$middle_name', '$last_name', '$phone', '$email')";

if (mysqli_query($conn, $sql)) {
    header("Location: customer.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>