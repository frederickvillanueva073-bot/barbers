<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';


include "db_connect.php";

$queue_id = $_POST['queue_id'];
$payment_method = $_POST['payment_method'];
$amount_paid = $_POST['amount_paid'];

// Get the service price from the queue
$query = mysqli_query($conn, "
    SELECT services.price 
    FROM queue 
    JOIN services ON queue.service_id = services.service_id 
    WHERE queue.queue_id = $queue_id
");
$row = mysqli_fetch_assoc($query);
$total = $row['price'];

$change_amount = $amount_paid - $total;

if ($change_amount < 0) {
    echo "<script>alert('Error: Amount paid is less than total price!'); window.history.back();</script>";
    exit();
}

// Insert into payments table
$sql = "INSERT INTO payments (queue_id, total, amount_paid, change_amount, payment_method, payment_date)
        VALUES ($queue_id, $total, $amount_paid, $change_amount, '$payment_method', CURDATE())";

if (mysqli_query($conn, $sql)) {
    // Mark queue as paid
    mysqli_query($conn, "UPDATE queue SET paid = 1 WHERE queue_id = $queue_id");
    header("Location: payments.php");
    exit();
} else {
    echo "Error: " . mysqli_error($conn);
}
?>