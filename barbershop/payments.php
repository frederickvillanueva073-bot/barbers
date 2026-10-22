
<?php
require_once 'auth.php';
requireLogin();
require_once 'db_connect.php';

$serviceSummary = "
    SELECT
        qs.queue_id,
        GROUP_CONCAT(
            s.service_name
            ORDER BY s.service_name
            SEPARATOR ', '
        ) AS service_names,
        SUM(s.price) AS total_price
    FROM queue_services qs
    JOIN services s ON s.service_id = qs.service_id
    GROUP BY qs.queue_id
";

$payments = mysqli_query($conn, "
    SELECT
        p.*,
        q.queue_number,
        q.date,
        q.paid,
        CONCAT_WS(
            ' ',
            c.first_name,
            NULLIF(c.middle_name, ''),
            c.last_name
        ) AS customer_name,
        COALESCE(ss.service_names, s.service_name) AS service_name
    FROM payments p
    JOIN `queue` q ON p.queue_id = q.queue_id
    JOIN customers c ON q.customer_id = c.customer_id
    LEFT JOIN services s ON q.service_id = s.service_id
    LEFT JOIN ($serviceSummary) ss ON ss.queue_id = q.queue_id
    ORDER BY p.payment_id DESC
");

$pending = mysqli_query($conn, "
    SELECT
        q.queue_id,
        q.queue_number,
        CONCAT_WS(
            ' ',
            c.first_name,
            NULLIF(c.middle_name, ''),
            c.last_name
        ) AS customer_name,
        COALESCE(ss.service_names, s.service_name) AS service_name,
        COALESCE(ss.total_price, s.price) AS total_price
    FROM `queue` q
    JOIN customers c ON q.customer_id = c.customer_id
    LEFT JOIN services s ON q.service_id = s.service_id
    LEFT JOIN ($serviceSummary) ss ON ss.queue_id = q.queue_id
    WHERE q.status = 'Done'
      AND q.paid = 0
    ORDER BY q.queue_id DESC
");

$totalSalesQuery = mysqli_query($conn, "
    SELECT COALESCE(SUM(total), 0) AS total_sales
    FROM payments
    WHERE payment_date = CURDATE()
");

if (!$payments || !$pending || !$totalSalesQuery) {
    error_log("Payments page query failed: " . mysqli_error($conn));
    exit("Unable to load payment information.");
}

$totalSales = (float) mysqli_fetch_assoc($totalSalesQuery)['total_sales'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments - Barbershop Queue System</title>
    <link rel="stylesheet" href="style.css">

    <style>
        td .action-container {
            display: flex;
            gap: 6px;
            align-items: center;
            flex-wrap: wrap;
        }
    </style>
</head>

<body>
    <nav>
        <span class="brand">Baldbershop ✄</span>
        <a href="home.php">Home</a>
        <a href="customer.php">Customers</a>
        <a href="barbers.php">Barbers</a>
        <a href="queue.php">Queue</a>
        <a href="payments.php" class="active">Payments</a>

        <form action="logout.php" method="POST"
              style="display: inline; margin: 0;">
            <button class="logout" type="submit">Logout</button>
        </form>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Record Payment</h2>
            <p>Only completed services that have not yet been paid are shown.</p>

            <form action="save_payment.php" method="POST">
                <label for="queueSelect">Queue Number & Customer</label>

                <select name="queue_id" id="queueSelect" required>
                    <option value="">-- Select Queue & Customer --</option>

                    <?php while ($row = mysqli_fetch_assoc($pending)) { ?>
                        <option
                            value="<?php echo (int) $row['queue_id']; ?>"
                            data-price="<?php echo (float) $row['total_price']; ?>">

                            <?php
                            echo htmlspecialchars(
                                $row['queue_number'] . ' - ' .
                                $row['customer_name'] . ' (' .
                                $row['service_name'] . ' - ₱' .
                                number_format((float) $row['total_price'], 2) . ')',
                                ENT_QUOTES,
                                'UTF-8'
                            );
                            ?>
                        </option>
                    <?php } ?>
                </select>

                <label for="totalPrice">Total Amount Due</label>
                <input
                    type="text"
                    id="totalPrice"
                    readonly
                    placeholder="Select queue first...">

                <label for="amountPaid">Amount Tendered (Cash Given)</label>
                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="amount_paid"
                    id="amountPaid"
                    placeholder="Enter amount paid"
                    required>

                <label for="changeAmount">Change</label>
                <input
                    type="text"
                    id="changeAmount"
                    readonly
                    placeholder="₱0.00">

                <label for="paymentMethod">Payment Method</label>
                <select name="payment_method" id="paymentMethod" required>
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Card">Card</option>
                </select>

                <button type="submit" style="margin-top: 15px;">
                    Save Payment
                </button>
            </form>
        </div>

        <div class="card">
            <h2>Payment History</h2>

            <div class="toolbar">
                <input
                    type="text"
                    id="search"
                    placeholder="Search customer or queue no...">

                <select id="filterMethod">
                    <option value="">All Methods</option>
                    <option value="Cash">Cash</option>
                    <option value="GCash">GCash</option>
                    <option value="Card">Card</option>
                </select>

                <input type="date" id="filterDate">
            </div>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Queue No.</th>
                            <th>Customer</th>
                            <th>Services</th>
                            <th>Total</th>
                            <th>Amount Paid</th>
                            <th>Change</th>
                            <th>Status</th>
                            <th>Method</th>
                            <th>Action</th>
                        </tr>
                    </thead>

                    <tbody id="paymentTable">
                        <?php while ($payment = mysqli_fetch_assoc($payments)) { ?>
                            <tr>
                                <td><?php echo (int) $payment['payment_id']; ?></td>

                                <td>
                                    <?php
                                    echo htmlspecialchars(
                                        $payment['payment_date'] ?? $payment['date'],
                                        ENT_QUOTES,
                                        'UTF-8'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($payment['queue_number'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($payment['customer_name'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($payment['service_name'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>₱<?php echo number_format((float) $payment['total'], 2); ?></td>
                                <td>₱<?php echo number_format((float) $payment['amount_paid'], 2); ?></td>
                                <td>₱<?php echo number_format((float) $payment['change_amount'], 2); ?></td>

                                <td>
                                    <?php echo (int) $payment['paid'] === 1 ? 'Paid' : 'Unpaid'; ?>
                                </td>

                                <td>
                                    <?php echo htmlspecialchars($payment['payment_method'], ENT_QUOTES, 'UTF-8'); ?>
                                </td>

                                <td>Completed</td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>
                Total Sales Today: ₱<?php echo number_format($totalSales, 2); ?>
            </h3>
        </div>
    </div>

    <script>
        var queueSelect = document.getElementById("queueSelect");
        var amountPaid = document.getElementById("amountPaid");
        var totalPrice = document.getElementById("totalPrice");
        var changeAmount = document.getElementById("changeAmount");

        function calculateChange() {
            var option = queueSelect.options[queueSelect.selectedIndex];
            var total = Number(option ? option.dataset.price : 0);
            var paid = Number(amountPaid.value);

            totalPrice.value = total > 0 ? "₱" + total.toFixed(2) : "";

            if (!amountPaid.value || total <= 0) {
                changeAmount.value = "₱0.00";
            } else if (paid < total) {
                changeAmount.value = "Insufficient amount";
            } else {
                changeAmount.value = "₱" + (paid - total).toFixed(2);
            }
        }

        queueSelect.addEventListener("change", calculateChange);
        amountPaid.addEventListener("input", calculateChange);

        function filterPayments() {
            var keyword = document.getElementById("search").value.toLowerCase();
            var method = document.getElementById("filterMethod").value;
            var date = document.getElementById("filterDate").value;
            var rows = document.querySelectorAll("#paymentTable tr");

            rows.forEach(function (row) {
                var rowDate = row.cells[1].textContent.trim();
                var queueNo = row.cells[2].textContent.toLowerCase();
                var customer = row.cells[3].textContent.toLowerCase();
                var rowMethod = row.cells[9].textContent.trim();

                var matchText =
                    queueNo.includes(keyword) ||
                    customer.includes(keyword);

                var matchMethod = method === "" || rowMethod === method;
                var matchDate = date === "" || rowDate === date;

                row.style.display =
                    matchText && matchMethod && matchDate ? "" : "none";
            });
        }

        document.getElementById("search").addEventListener("input", filterPayments);
        document.getElementById("filterMethod").addEventListener("change", filterPayments);
        document.getElementById("filterDate").addEventListener("change", filterPayments);
    </script>
</body>
</html>
