
<?php
require_once 'auth.php';
requireLogin();
require_once 'db_connect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    exit('Invalid queue ID.');
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT * FROM `queue` WHERE queue_id = ?'
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$queue = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$queue) {
    exit('Queue entry not found.');
}

$customers = mysqli_query(
    $conn,
    'SELECT * FROM customers ORDER BY last_name'
);

$barbers = mysqli_query(
    $conn,
    'SELECT * FROM barbers ORDER BY last_name'
);

$services = mysqli_query(
    $conn,
    'SELECT * FROM services ORDER BY service_name'
);

$stmt = mysqli_prepare(
    $conn,
    'SELECT service_id FROM queue_services WHERE queue_id = ?'
);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$selectedServices = [];

while ($row = mysqli_fetch_assoc($result)) {
    $selectedServices[] = (int) $row['service_id'];
}

mysqli_stmt_close($stmt);

if (empty($selectedServices) && !empty($queue['service_id'])) {
    $selectedServices[] = (int) $queue['service_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Queue - Baldbershop</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container">
        <div class="card">
            <h2>Edit Queue</h2>

            <form action="update_queue.php" method="POST">
                <input
                    type="hidden"
                    name="queue_id"
                    value="<?php echo (int) $queue['queue_id']; ?>">

                <label for="customer">Customer</label>
                <select name="customer_id" id="customer" required>
                    <?php while ($customer = mysqli_fetch_assoc($customers)) {
                        $name = trim(
                            $customer['first_name'] . ' ' .
                            ($customer['middle_name'] ?? '') . ' ' .
                            $customer['last_name']
                        );
                    ?>
                        <option
                            value="<?php echo (int) $customer['customer_id']; ?>"
                            <?php echo (int) $customer['customer_id'] === (int) $queue['customer_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="barber">Barber</label>
                <select name="barber_id" id="barber" required>
                    <?php while ($barber = mysqli_fetch_assoc($barbers)) {
                        $name = trim(
                            $barber['first_name'] . ' ' .
                            ($barber['middle_name'] ?? '') . ' ' .
                            $barber['last_name']
                        );
                    ?>
                        <option
                            value="<?php echo (int) $barber['barber_id']; ?>"
                            <?php echo (int) $barber['barber_id'] === (int) $queue['barber_id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name, ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="service_ids">Services</label>
                <select
                    name="service_ids[]"
                    id="service_ids"
                    multiple
                    required
                    size="5">
                    <?php while ($service = mysqli_fetch_assoc($services)) { ?>
                        <option
                            value="<?php echo (int) $service['service_id']; ?>"
                            data-price="<?php echo (float) $service['price']; ?>"
                            <?php echo in_array((int) $service['service_id'], $selectedServices, true) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($service['service_name'], ENT_QUOTES, 'UTF-8'); ?>
                            - ₱<?php echo number_format((float) $service['price'], 2); ?>
                        </option>
                    <?php } ?>
                </select>

                <p>Hold Ctrl while clicking to select multiple services.</p>

                <label for="totalPrice">Total Price</label>
                <input type="text" id="totalPrice" readonly>

                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <?php foreach (['Waiting', 'In Service', 'Done'] as $status) { ?>
                        <option
                            value="<?php echo $status; ?>"
                            <?php echo $queue['status'] === $status ? 'selected' : ''; ?>>
                            <?php echo $status; ?>
                        </option>
                    <?php } ?>
                </select>

                <button type="submit">Update Queue</button>
                <a href="queue.php">Cancel</a>
            </form>
        </div>
    </div>

    <script>
        var services = document.getElementById("service_ids");
        var totalPrice = document.getElementById("totalPrice");

        function calculateTotal() {
            var total = 0;

            for (var i = 0; i < services.selectedOptions.length; i++) {
                total += Number(services.selectedOptions[i].dataset.price || 0);
            }

            totalPrice.value = "₱" + total.toFixed(2);
        }

        services.addEventListener("change", calculateTotal);
        calculateTotal();
    </script>
</body>
</html>
