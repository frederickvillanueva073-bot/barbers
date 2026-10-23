
<?php
require_once 'auth.php';
requireAdmin();

require_once 'db_connect.php';




include "db_connect.php";

$id = (int) $_GET['id'];

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

$result = mysqli_query($conn, "SELECT * FROM barbers WHERE barber_id = $id");

$barber = mysqli_fetch_assoc($result);

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Barber</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

<div class="container">

    <div class="card">

        <h2>Edit Barber / Stylist</h2>

        <form action="update_barber.php" method="POST">

            <input type="hidden" name="barber_id" value="<?php echo $barber['barber_id']; ?>">

            <label>First Name</label>
            <input type="text" name="first_name" value="<?php echo $barber['first_name']; ?>" required>

            <label>Middle Name (optional)</label>
            <input type="text" name="middle_name" value="<?php echo $barber['middle_name']; ?>">

            <label>Last Name</label>
            <input type="text" name="last_name" value="<?php echo $barber['last_name']; ?>" required>

            <label>Specialty</label>
            <select name="specialty">
                <option value="Haircut" <?php if($barber['specialty'] == 'Haircut') echo 'selected'; ?>>Haircut</option>
                <option value="Shave" <?php if($barber['specialty'] == 'Shave') echo 'selected'; ?>>Shave</option>
                <option value="Hair Color" <?php if($barber['specialty'] == 'Hair Color') echo 'selected'; ?>>Hair Color</option>
                <option value="Hair Spa / Rebond" <?php if($barber['specialty'] == 'Hair Spa / Rebond') echo 'selected'; ?>>Hair Spa / Rebond</option>
                <option value="All-around" <?php if($barber['specialty'] == 'All-around') echo 'selected'; ?>>All-around</option>
            </select>

            <label>Status</label>
            <select name="status">
                <option value="Available" <?php if($barber['status'] == 'Available') echo 'selected'; ?>>Available</option>
                <option value="On Break" <?php if($barber['status'] == 'On Break') echo 'selected'; ?>>On Break</option>
                <option value="Off Duty" <?php if($barber['status'] == 'Off Duty') echo 'selected'; ?>>Off Duty</option>
            </select>

            <button type="submit">
                Update Barber
            </button>

        </form>

    </div>

</div>

</body>

</html>