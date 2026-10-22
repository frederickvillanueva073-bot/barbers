<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';


include "db_connect.php";

$id = $_GET['id'];


// Check if queue has payment
$check = mysqli_query($conn, "
SELECT * FROM payments 
WHERE queue_id='$id'
");


if(mysqli_num_rows($check) > 0){

    echo "<script>
    alert('Cannot delete this queue because it already has a payment record.');
    window.location='queue.php';
    </script>";

} else {

    mysqli_query($conn, "
    DELETE FROM queue 
    WHERE queue_id='$id'
    ");

    header("Location: queue.php");

}

?>