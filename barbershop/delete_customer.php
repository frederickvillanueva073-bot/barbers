<?php

include "db_connect.php";

$id = $_GET['id'];


// Check if customer has queue records
$check = mysqli_query($conn, "
SELECT * FROM queue 
WHERE customer_id='$id'
");


if(mysqli_num_rows($check) > 0){

    echo "<script>
    alert('Cannot delete customer because they have existing queue records.');
    window.location='customer.php';
    </script>";

} else {

    mysqli_query($conn, "
    DELETE FROM customers 
    WHERE customer_id='$id'
    ");

    header("Location: customer.php");

}

?>