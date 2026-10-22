
<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';


include "db_connect.php";

$payments = mysqli_query($conn, "

SELECT 
payments.*,
queue.queue_number,
queue.date,
queue.paid,
CONCAT_WS(' ', customers.first_name, NULLIF(customers.middle_name, ''), customers.last_name) AS customer_name,
services.service_name,
services.price

FROM payments

JOIN queue
ON payments.queue_id = queue.queue_id

JOIN customers
ON queue.customer_id = customers.customer_id

JOIN services
ON queue.service_id = services.service_id

");

$totalSalesQuery = mysqli_query($conn, "

SELECT SUM(amount_paid) AS total_sales

FROM payments

WHERE payment_date = CURDATE()

");

$totalSales = mysqli_fetch_assoc($totalSalesQuery)['total_sales'];

if (!$totalSales) {
    $totalSales = 0;
}

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
            <p>Only finished (Done) services that are not yet paid are shown.</p>
            <form action="save_payment.php" method="POST">
                <label>Queue Number & Customer</label>

                <select name="queue_id" id="queueSelect" required>

                    <option value="">
                        -- Select Queue & Customer --
                    </option>

                    <?php
                    $pending = mysqli_query($conn, "
                        SELECT queue.queue_id, queue.queue_number, services.price,
                        CONCAT_WS(' ', customers.first_name, NULLIF(customers.middle_name, ''), customers.last_name) AS customer_name,
                        services.service_name
                        FROM queue
                        JOIN customers ON queue.customer_id = customers.customer_id
                        JOIN services ON queue.service_id = services.service_id
                        WHERE queue.status='Done'
                        AND queue.paid=0
                    ");

                    while ($row = mysqli_fetch_assoc($pending)) {
                    ?>

                        <option value="<?php echo $row['queue_id']; ?>" data-price="<?php echo $row['price']; ?>">
                            <?php echo $row['queue_number'] . ' - ' . $row['customer_name'] . ' (' . $row['service_name'] . ' - ₱' . $row['price'] . ')'; ?>
                        </option>

                    <?php } ?>
                </select>

                <label>Total Amount Due</label>
                <input type="text" id="totalPrice" readonly placeholder="Select queue first...">
                <input type="hidden" name="total" id="hiddenTotal">

                <label>Amount Tendered (Cash Given)</label>
                <input type="number" step="0.01" name="amount_paid" id="amountPaid" placeholder="e.g. 500" required>

                <label>Change</label>
                <input type="text" id="changeAmount" readonly placeholder="₱0.00">

                <label>Payment Method</label>

                <select name="payment_method" required>

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

                <input type="text" id="search" placeholder="Search customer or queue no...">

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
                            <th>Service</th>
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

                                <td><?php echo $payment['payment_id']; ?></td>
                                <td><?php echo $payment['payment_date'] ?? $payment['date']; ?></td>
                                <td><?php echo $payment['queue_number']; ?></td>
                                <td><?php echo $payment['customer_name']; ?></td>
                                <td><?php echo $payment['service_name']; ?></td>
                                <td>₱<?php echo number_format($payment['total'], 2); ?></td>
                                <td>₱<?php echo number_format($payment['amount_paid'], 2); ?></td>
                                <td>₱<?php echo number_format($payment['change_amount'], 2); ?></td>
                                <td>
                                    <?php
                                    if ($payment['paid'] == 1) {
                                        echo "Paid";
                                    } else {
                                        echo "Unpaid";
                                    }
                                    ?>
                                </td>
                                <td><?php echo $payment['payment_method']; ?></td>
                                <td>Completed</td>

                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
            <h3>
                Total Sales Today
                ₱<?php echo number_format($totalSales, 2); ?>
            </h3>
        </div>
    </div>

    <script src="storage.js"></script>
    <script>
        // Auto-fill price and calculate change
        document.getElementById("queueSelect").addEventListener("change", function() {
            let selectedOpt = this.options[this.selectedIndex];
            let price = selectedOpt.getAttribute("data-price") || 0;
            document.getElementById("totalPrice").value = price ? "₱" + Number(price).toFixed(2) : "";
            document.getElementById("hiddenTotal").value = price;
            calculateChange();
        });

        document.getElementById("amountPaid").addEventListener("input", calculateChange);

        function calculateChange() {
            let total = parseFloat(document.getElementById("hiddenTotal").value) || 0;
            let paid = parseFloat(document.getElementById("amountPaid").value) || 0;
            let change = paid - total;
            document.getElementById("changeAmount").value = change >= 0 ? "₱" + change.toFixed(2) : "Insufficient amount";
        }

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

                var matchText = queueNo.indexOf(keyword) !== -1 || customer.indexOf(keyword) !== -1;
                var matchMethod = method === "" || rowMethod === method;
                var matchDate = date === "" || rowDate === date;

                if (matchText && matchMethod && matchDate) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        document.getElementById("search").addEventListener("input", filterPayments);
        document.getElementById("filterMethod").addEventListener("change", filterPayments);
        document.getElementById("filterDate").addEventListener("change", filterPayments);
    </script>
</body>

</html>