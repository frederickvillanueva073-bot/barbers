<?php

include "db_connect.php";

$id = $_GET['id'];

$sql = "

UPDATE queue

SET 
status='In Service',
start_time=CURTIME()

WHERE queue_id='$id'

";


if(mysqli_query($conn, $sql)){

    header("Location: queue.php");

}else{

    echo "Error: " . mysqli_error($conn);

}

?>