
<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';




include "db_connect.php";

$id = $_GET['id'];

$result = mysqli_query($conn, "SELECT * FROM queue WHERE queue_id='$id'");
$queue = mysqli_fetch_assoc($result);

$customers = mysqli_query($conn, "SELECT * FROM customers");
$barbers = mysqli_query($conn, "SELECT * FROM barbers");
$services = mysqli_query($conn, "SELECT * FROM services");

?>

<!DOCTYPE html>
<html>

<head>
    <title>Edit Queue</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <div class="container">

        <div class="card">

            <h2>Edit Queue</h2>

            <form action="update_queue.php" method="POST">

                <input type="hidden" name="queue_id" value="<?php echo $queue['queue_id']; ?>">

                <label>Customer</label>
                <select name="customer_id">
                    <?php while ($customer = mysqli_fetch_assoc($customers)) { ?>
                        <option value="<?php echo $customer['customer_id']; ?>" <?php if ($customer['customer_id'] == $queue['customer_id']) echo 'selected'; ?>>
                            <?php echo $customer['first_name'] . ' ' . $customer['middle_name'] . ' ' . $customer['last_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Barber</label>
                <select name="barber_id">
                    <?php while ($barber = mysqli_fetch_assoc($barbers)) { ?>
                        <option value="<?php echo $barber['barber_id']; ?>" <?php if ($barber['barber_id'] == $queue['barber_id']) echo 'selected'; ?>>
                            <?php echo $barber['first_name'] . ' ' . $barber['middle_name'] . ' ' . $barber['last_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Service</label>
                <select name="service_id">
                    <?php while ($service = mysqli_fetch_assoc($services)) { ?>
                        <option value="<?php echo $service['service_id']; ?>" <?php if ($service['service_id'] == $queue['service_id']) echo 'selected'; ?>>
                            <?php echo $service['service_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Status</label>
                <select name="status">
                    <option value="Waiting" <?php if ($queue['status'] == 'Waiting') echo 'selected'; ?>>Waiting</option>
                    <option value="In Service" <?php if ($queue['status'] == 'In Service') echo 'selected'; ?>>In Service</option>
                    <option value="Done" <?php if ($queue['status'] == 'Done') echo 'selected'; ?>>Done</option>
                </select>

                <button type="submit">
                    Update Queue
                </button>

            </form>

        </div>

    </div>

</body>

</html>