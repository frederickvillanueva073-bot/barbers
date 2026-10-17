<?php

include "db_connect.php";

$name = $_POST['name'];
$specialty = $_POST['specialty'];
$status = $_POST['status'];

$sql = "INSERT INTO barbers (name, specialty, status)
        VALUES ('$name', '$specialty', '$status')";

if(mysqli_query($conn, $sql)){

    header("Location: barbers.php");

}else{

    echo "Error: " . mysqli_error($conn);

}

?>