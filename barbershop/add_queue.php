<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';


include "db_connect.php";


$customer_id = $_POST['customer_id'];
$barber_id = $_POST['barber_id'];
$service_id = $_POST['service_id'];


// generate queue number

$result = mysqli_query($conn, "SELECT COUNT(*) as total FROM queue");

$row = mysqli_fetch_assoc($result);

$queue_number = "Q-" . str_pad($row['total'] + 1, 3, "0", STR_PAD_LEFT);


// insert data

$sql = "INSERT INTO queue
(customer_id, barber_id, service_id, queue_number, date, status, paid)

VALUES
('$customer_id',
'$barber_id',
'$service_id',
'$queue_number',
CURDATE(),
'Waiting',
0)";


if(mysqli_query($conn, $sql)){

    header("Location: queue.php");

}else{

    echo "Error: " . mysqli_error($conn);

}

?>