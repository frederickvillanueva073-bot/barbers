
<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';



include "db_connect.php";

$resultQueue = mysqli_query($conn, "
SELECT 
queue.*,
CONCAT_WS(' ', customers.first_name, NULLIF(customers.middle_name, ''), customers.last_name) AS customer_name,
CONCAT_WS(' ', barbers.first_name, NULLIF(barbers.middle_name, ''), barbers.last_name) AS barber_name,
services.service_name,
services.price

FROM queue

JOIN customers 
ON queue.customer_id = customers.customer_id

JOIN barbers
ON queue.barber_id = barbers.barber_id

JOIN services
ON queue.service_id = services.service_id
");

$customers = mysqli_query($conn, "SELECT * FROM customers");

// Modified query to check if a barber is currently "In Service"
$barbers = mysqli_query($conn, "
SELECT barbers.*, 
(SELECT COUNT(*) FROM queue WHERE queue.barber_id = barbers.barber_id AND queue.status = 'In Service') AS is_busy 
FROM barbers
");

$services = mysqli_query($conn, "SELECT * FROM services");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue - Barbershop Queue System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        td .action-container {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }
        td .action-container a {
            margin: 0;
        }
    </style>
</head>

<body>
    <nav>
        <span class="brand">Baldbershop ✄</span>
        <a href="home.php">Home</a>
        <a href="customer.php">Customers</a>
        <a href="barbers.php">Barbers</a>
        <a href="queue.php" class="active">Queue</a>
        <a href="payments.php">Payments</a>
        
        <form action="logout.php" method="POST"
            style="display: inline; margin: 0;">
            <button class="logout" type="submit">Logout</button>
        </form>

    </nav>

    <div class="container">
        <div class="card">
            <h2>Add to Queue / Appointment</h2>
            <form action="add_queue.php" method="POST">
                <label for="customer">Customer</label>
                <select name="customer_id" id="customer" required>
                    <option value="">-- Select customer --</option>
                    <?php while($customer = mysqli_fetch_assoc($customers)) { ?>
                        <option value="<?php echo $customer['customer_id']; ?>">
                            <?php echo $customer['first_name'] . ' ' . ($customer['middle_name'] ? $customer['middle_name'] . ' ' : '') . $customer['last_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="barber">Barber / Stylist</label>
                <select name="barber_id" id="barber" required>
                    <option value="">-- Select barber --</option>
                    <?php while($barber = mysqli_fetch_assoc($barbers)) { 
                        $barberName = $barber['first_name'] . ' ' . ($barber['middle_name'] ? $barber['middle_name'] . ' ' : '') . $barber['last_name'];
                        $isBusy = $barber['is_busy'] > 0;
                    ?>
                        <option value="<?php echo $barber['barber_id']; ?>" <?php echo $isBusy ? 'disabled style="color: #999;"' : ''; ?>>
                            <?php echo $barberName . ($isBusy ? ' (Busy / In Service)' : ''); ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="service">Service</label>
                <select name="service_id" id="service" required>
                    <option value="">-- Select service --</option>
                    <?php while($service = mysqli_fetch_assoc($services)) { ?>
                        <option 
                            value="<?php echo $service['service_id']; ?>"
                            data-price="<?php echo $service['price']; ?>">
                            <?php echo $service['service_name']; ?>
                        </option>
                    <?php } ?>
                </select>

                <label>Price</label>
                <input type="text" name="price" id="price" readonly>

                <p class="error" id="error"></p>
                <button type="submit">Get Queue Number</button>
            </form>
        </div>

        <div class="card">
            <h2>Queue List</h2>
            <div class="toolbar">
                <input type="text" id="search" placeholder="Search customer, barber, or queue no...">
                <select id="filterStatus">
                    <option value="">All Status</option>
                    <option value="Waiting">Waiting</option>
                    <option value="In Service">In Service</option>
                    <option value="Done">Done</option>
                </select>
                <input type="date" id="filterDate">
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Queue No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Barber</th>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="queueTable">
                        <?php while($queue = mysqli_fetch_assoc($resultQueue)) { ?>
                        <tr>
                            <td><?php echo $queue['queue_number']; ?></td>
                            <td><?php echo $queue['date']; ?></td>
                            <td><?php echo $queue['customer_name']; ?></td>
                            <td><?php echo $queue['barber_name']; ?></td>
                            <td><?php echo $queue['service_name']; ?></td>
                            <td><?php echo $queue['price']; ?></td>
                            <td><?php echo $queue['start_time'] ? $queue['start_time'] : "-"; ?></td>
                            <td><?php echo $queue['end_time'] ? $queue['end_time'] : "-"; ?></td>
                            <td><?php echo $queue['status']; ?></td>
                            <td>
                                <div class="action-container">
                                    <?php if($queue['status'] == "Waiting"){ ?>
                                        <a href="start_service.php?id=<?php echo $queue['queue_id']; ?>">Start</a>
                                    <?php } elseif($queue['status'] == "In Service"){ ?>
                                        <a href="complete_service.php?id=<?php echo $queue['queue_id']; ?>">Complete</a>
                                    <?php } else { ?>
                                        <span class="badge Done">Done</span>
                                    <?php } ?>

                                    <a href="edit_queue.php?id=<?php echo $queue['queue_id']; ?>">Edit</a>
                                    
                                    <a href="delete_queue.php?id=<?php echo $queue['queue_id']; ?>"
                                    onclick="return confirm('Are you sure you want to delete this queue?');" style="background: #dc2626; color: white;">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="storage.js"></script>
    <script>
        document.getElementById("service").addEventListener("change", function(){
            let price = this.options[this.selectedIndex].getAttribute("data-price");
            document.getElementById("price").value = price || "";
        });

        function filterQueue() {
            var keyword = document.getElementById("search").value.toLowerCase();
            var status = document.getElementById("filterStatus").value;
            var date = document.getElementById("filterDate").value;
            var rows = document.querySelectorAll("#queueTable tr");

            rows.forEach(function (row) {
                var queueNo = row.cells[0].textContent.toLowerCase();
                var rowDate = row.cells[1].textContent.trim();
                var customer = row.cells[2].textContent.toLowerCase();
                var barber = row.cells[3].textContent.toLowerCase();
                var rowStatus = row.cells[8].textContent.trim();

                var matchText = queueNo.indexOf(keyword) !== -1 || customer.indexOf(keyword) !== -1 || barber.indexOf(keyword) !== -1;
                var matchStatus = status === "" || rowStatus === status;
                var matchDate = date === "" || rowDate === date;

                if (matchText && matchStatus && matchDate) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        document.getElementById("search").addEventListener("input", filterQueue);
        document.getElementById("filterStatus").addEventListener("change", filterQueue);
        document.getElementById("filterDate").addEventListener("change", filterQueue);
    </script>
</body>

</html>