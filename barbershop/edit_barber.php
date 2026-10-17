<?php

include "db_connect.php";

$id = $_GET['id'];

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

            <input type="hidden" name="barber_id" 
            value="<?php echo $barber['barber_id']; ?>">


            <label>Full Name</label>
            <input type="text" 
            name="name"
            value="<?php echo $barber['name']; ?>">


            <label>Specialty</label>

            <select name="specialty">

                <option value="Haircut">
                    Haircut
                </option>

                <option value="Shave">
                    Shave
                </option>

                <option value="Hair Color">
                    Hair Color
                </option>

                <option value="Hair Spa / Rebond">
                    Hair Spa / Rebond
                </option>

                <option value="All-around">
                    All-around
                </option>

            </select>


            <label>Status</label>

            <select name="status">

                <option value="Available">
                    Available
                </option>

                <option value="On Break">
                    On Break
                </option>

                <option value="Off Duty">
                    Off Duty
                </option>

            </select>


            <button type="submit">
                Update Barber
            </button>


        </form>


    </div>

</div>


</body>

</html>