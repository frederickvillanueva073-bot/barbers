
<?php
require_once 'auth.php';
requireAdmin();

require_once 'db_connect.php';



include "db_connect.php";

$id = $_POST['barber_id'];
$first_name = $_POST['first_name'];
$middle_name = $_POST['middle_name'];
$last_name = $_POST['last_name'];
$specialty = $_POST['specialty'];
$status = $_POST['status'];

$sql = "UPDATE barbers 
        SET first_name='$first_name',
            middle_name='$middle_name',
            last_name='$last_name',
            specialty='$specialty',
            status='$status'
        WHERE barber_id=$id";

if(mysqli_query($conn, $sql)){

    header("Location: barbers.php");

} else {

    echo "Error: " . mysqli_error($conn);

}

?>