<?php

include "db_connect.php";

$id = $_GET['id'];

$sql = "DELETE FROM barbers WHERE barber_id=$id";

if(mysqli_query($conn, $sql)){

    header("Location: barbers.php");

} else {

    echo "Error: " . mysqli_error($conn);

}

?>