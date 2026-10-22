
<?php
require_once 'auth.php';
requireAdmin();

require_once 'db_connect.php';


include "db_connect.php";

$id = $_GET['id'];

$check = mysqli_query($conn, "SELECT * FROM queue WHERE barber_id = $id");

if (mysqli_num_rows($check) > 0) {
    echo "<script>alert('Cannot delete barber: They have existing queue or appointment assignments!'); window.location.href='barbers.php';</script>";
} else {
    mysqli_query($conn, "DELETE FROM barbers WHERE barber_id = $id");
    header("Location: barbers.php");
}
?>