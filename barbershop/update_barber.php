<?php

include "db_connect.php";


$id = $_POST['barber_id'];
$name = $_POST['name'];
$specialty = $_POST['specialty'];
$status = $_POST['status'];


$sql = "UPDATE barbers 
        SET name='$name',
            specialty='$specialty',
            status='$status'
        WHERE barber_id=$id";


if(mysqli_query($conn, $sql)){

    header("Location: barbers.php");

} else {

    echo "Error: " . mysqli_error($conn);

}

?>