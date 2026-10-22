<?php

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "barbershop_db"
);

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}



?>