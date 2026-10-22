
<?php
require_once 'auth.php';
requireLogin();
require_once 'db_connect.php';

$resultQueue = mysqli_query($conn, "
    SELECT
        q.*,
        CONCAT_WS(' ', c.first_name, NULLIF(c.middle_name, ''), c.last_name) AS customer_name,
        CONCAT_WS(' ', b.first_name, NULLIF(b.middle_name, ''), b.last_name) AS barber_name,
        COALESCE(qs.service_names, s.service_name) AS service_name,
        COALESCE(qs.total_price, s.price) AS price
    FROM `queue` q
    JOIN customers c ON q.customer_id = c.customer_id
    JOIN barbers b ON q.barber_id = b.barber_id
    LEFT JOIN services s ON q.service_id = s.service_id
    LEFT JOIN (
        SELECT
            qsv.queue_id,
            GROUP_CONCAT(
                sv.service_name
                ORDER BY sv.service_name
                SEPARATOR ', '
            ) AS service_names,
            SUM(sv.price) AS total_price
        FROM queue_services qsv
        JOIN services sv ON qsv.service_id = sv.service_id
        GROUP BY qsv.queue_id
    ) qs ON q.queue_id = qs.queue_id
    ORDER BY q.queue_id DESC
");

$customers = mysqli_query($conn, "SELECT * FROM customers ORDER BY last_name");

$barbers = mysqli_query($conn, "
    SELECT b.*,
        (
            SELECT COUNT(*)
            FROM `queue` q
            WHERE q.barber_id = b.barber_id
              AND q.status = 'In Service'
        ) AS is_busy
    FROM barbers b
    ORDER BY b.last_name
");

$services = mysqli_query($conn, "SELECT * FROM services ORDER BY service_name");

if (!$resultQueue || !$customers || !$barbers || !$services) {
    error_log("Queue page query failed: " . mysqli_error($conn));
    exit("Unable to load queue information.");
}
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

        #service {
            min-height: 120px;
        }

        .form-hint {
            font-size: 13px;
            color: #666;
            margin-top: 5px;
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
                    <?php while ($customer = mysqli_fetch_assoc($customers)) { ?>
                        <option value="<?php echo (int) $customer['customer_id']; ?>">
                            <?php
                            echo htmlspecialchars(
                                trim(
                                    $customer['first_name'] . ' ' .
                                    ($customer['middle_name'] ? $customer['middle_name'] . ' ' : '') .
                                    $customer['last_name']
                                ),
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="barber">Barber / Stylist</label>
                <select name="barber_id" id="barber" required>
                    <option value="">-- Select barber --</option>
                    <?php while ($barber = mysqli_fetch_assoc($barbers)) {
                        $barberName = trim(
                            $barber['first_name'] . ' ' .
                            ($barber['middle_name'] ? $barber['middle_name'] . ' ' : '') .
                            $barber['last_name']
                        );

                        $isBusy = (int) $barber['is_busy'] > 0;
                    ?>
                        <option
                            value="<?php echo (int) $barber['barber_id']; ?>"
                            <?php echo $isBusy ? 'disabled' : ''; ?>>
                            <?php
                            echo htmlspecialchars($barberName, ENT_QUOTES, 'UTF-8');
                            echo $isBusy ? ' (Busy / In Service)' : '';
                            ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="service">Services</label>
                <select name="service_ids[]" id="service" multiple required size="5">
                    <?php while ($service = mysqli_fetch_assoc($services)) { ?>
                        <option
                            value="<?php echo (int) $service['service_id']; ?>"
                            data-price="<?php echo (float) $service['price']; ?>">
                            <?php
                            echo htmlspecialchars(
                                $service['service_name'],
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                            - ₱<?php echo number_format((float) $service['price'], 2); ?>
                        </option>
                    <?php } ?>
                </select>

                <p class="form-hint">
                    Hold Ctrl while clicking to select multiple services.
                </p>

                <label for="price">Total Price</label>
                <input type="text" id="price" readonly placeholder="₱0.00">

                <p class="error" id="error"></p>

                <button type="submit">Get Queue Number</button>
            </form>
        </div>

        <div class="card">
            <h2>Queue List</h2>

            <div class="toolbar">
                <input
                    type="text"
                    id="search"
                    placeholder="Search customer, barber, or queue no...">

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
                            <th>Services</th>
                            <th>Total Price</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="queueTable">
                        <?php while ($queue = mysqli_fetch_assoc($resultQueue)) { ?>
                            <tr>
                                <td>
                                    <?php echo htmlspecialchars($queue['queue_number'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($queue['date'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($queue['customer_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($queue['barber_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($queue['service_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    ₱<?php echo number_format((float) ($queue['price'] ?? 0), 2); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($queue['start_time'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($queue['end_time'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($queue['status'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <div class="action-container">
                                        <?php if ($queue['status'] === 'Waiting') { ?>
                                            <a href="start_service.php?id=<?php echo (int) $queue['queue_id']; ?>">
                                                Start
                                            </a>
                                        <?php } elseif ($queue['status'] === 'In Service') { ?>
                                            <a href="complete_service.php?id=<?php echo (int) $queue['queue_id']; ?>">
                                                Complete
                                            </a>
                                        <?php } else { ?>
                                            <span class="badge Done">Done</span>
                                        <?php } ?>

                                        <a href="edit_queue.php?id=<?php echo (int) $queue['queue_id']; ?>">
                                            Edit
                                        </a>

                                        <a
                                            href="delete_queue.php?id=<?php echo (int) $queue['queue_id']; ?>"
                                            onclick="return confirm('Are you sure you want to delete this queue?');"
                                            style="background: #dc2626; color: white;">
                                            Delete
                                        </a>
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
        document.getElementById("service").addEventListener("change", function () {
            var total = 0;

            for (var i = 0; i < this.selectedOptions.length; i++) {
                total += Number(this.selectedOptions[i].dataset.price || 0);
            }

            document.getElementById("price").value = "₱" + total.toFixed(2);
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

                var matchText =
                    queueNo.includes(keyword) ||
                    customer.includes(keyword) ||
                    barber.includes(keyword);

                var matchStatus = status === "" || rowStatus === status;
                var matchDate = date === "" || rowDate === date;

                row.style.display =
                    matchText && matchStatus && matchDate ? "" : "none";
            });
        }

        document.getElementById("search").addEventListener("input", filterQueue);
        document.getElementById("filterStatus").addEventListener("change", filterQueue);
        document.getElementById("filterDate").addEventListener("change", filterQueue);
    </script>
</body>
</html>
