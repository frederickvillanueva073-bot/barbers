
<?php
require_once 'auth.php';
requireAdmin();

require_once 'db_connect.php';



include "db_connect.php";

$id = (int) $_POST['barber_id'];

if ($id <= 0) {
    exit('Invalid barber ID.');
}

$check = mysqli_query($conn, "
    SELECT queue_id
    FROM `queue`
    WHERE barber_id = $id
    AND (status = 'Waiting' OR status = 'In Service')
    LIMIT 1
");

if (!$check) {
    exit('Unable to check the barber queue. Please try again.');
}

if (mysqli_num_rows($check) > 0) {
    echo "<script>
        alert('Cannot edit this barber while they have Waiting or In Service customers.');
        window.location.href = 'barbers.php';
    </script>";
    exit();
}

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