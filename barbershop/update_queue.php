<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';

include "db_connect.php";
date_default_timezone_set("Asia/Manila");

$id = $_POST['queue_id'];
$customer_id = $_POST['customer_id'];
$barber_id = $_POST['barber_id'];
$service_id = $_POST['service_id'];
$status = $_POST['status'];


// Check current queue data
$current = mysqli_query($conn, 
"SELECT start_time FROM queue WHERE queue_id='$id'");

$row = mysqli_fetch_assoc($current);


$start_time = $row['start_time'];


// If status becomes In Service, record start time
if($status == "In Service" && empty($start_time)){

    $start_time = date("H:i:s");

}


// If status becomes Done, record end time
if($status == "Done"){

    $end_time = date("H:i:s");

}
else{

    $end_time = NULL;

}


// Update queue

$sql = "UPDATE queue SET

customer_id='$customer_id',
barber_id='$barber_id',
service_id='$service_id',
status='$status',
start_time=" . 
($start_time ? "'$start_time'" : "NULL") . ",
end_time=" .
($end_time ? "'$end_time'" : "NULL") . "

WHERE queue_id='$id'";


if(mysqli_query($conn, $sql)){

    header("Location: queue.php");

}
else{

    echo "Error: " . mysqli_error($conn);

}

?>