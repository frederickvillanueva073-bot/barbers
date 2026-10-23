<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';


include "db_connect.php";

$id = $_GET['id'];

$sql = "UPDATE queue 
SET paid = 1
WHERE queue_id='$id'";


if(mysqli_query($conn, $sql)){

    header("Location: payments.php");

}
else{

    echo "Error: " . mysqli_error($conn);

}

?>