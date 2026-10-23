<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$roleLabel = $isAdmin ? 'Admin' : 'Staff';

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers");
$row = mysqli_fetch_assoc($result);
$totalCustomers = $row['total'];

$services = mysqli_query($conn, "SELECT * FROM services");

$resultBarbers = mysqli_query($conn, "SELECT COUNT(*) AS total FROM barbers");
$rowBarbers = mysqli_fetch_assoc($resultBarbers);
$totalBarbers = $rowBarbers['total'];

$resultWaiting = mysqli_query($conn, "SELECT COUNT(*) AS total FROM queue WHERE status='Waiting'");
$rowWaiting = mysqli_fetch_assoc($resultWaiting);
$totalWaiting = $rowWaiting['total'];

$resultSales = mysqli_query($conn, "SELECT SUM(total) AS total FROM payments WHERE payment_date = CURDATE()");
$rowSales = mysqli_fetch_assoc($resultSales);
$totalSales = $rowSales['total'] ?? 0;

$allTimeSales = 0;

if ($isAdmin) {
    $resultAllSales = mysqli_query($conn, "SELECT SUM(total) AS total FROM payments");
    $rowAllSales = mysqli_fetch_assoc($resultAllSales);
    $allTimeSales = $rowAllSales['total'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - Barbershop Queue System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>

    <nav>
        <span class="brand">Baldbershop ✄</span>
        <a href="home.php" class="active">Home</a>
        <a href="customer.php">Customers</a>
        <a href="barbers.php">Barbers</a>
        <a href="queue.php">Queue</a>
        <a href="payments.php">Payments</a>
<?php if ($isAdmin) { ?>
    <a href="reset_password.php">Reset Staff Password</a>
<?php } ?>
        <form action="logout.php" method="POST"
              style="display: inline; margin: 0 0 0 auto;">
            <button class="logout" type="submit">Logout</button>
        </form>
    </nav>

    <div class="container">

        <div class="card">
            <h1>Welcome, <?php echo $roleLabel; ?>!</h1>
            <p>
                Barbershop / Salon Queue & Appointment System.
                Today is <span id="todayDate"></span>.
            </p>
        </div>

        <div class="stats">

            <div class="stat-box">
                <div class="number" id="countCustomers">
                    <?php echo $totalCustomers; ?>
                </div>
                <div>Customers</div>
            </div>

            <div class="stat-box">
                <div class="number" id="countBarbers">
                    <?php echo $totalBarbers; ?>
                </div>
                <div>Barbers / Stylists</div>
            </div>

            <div class="stat-box">
                <div class="number" id="countWaiting">
                    <?php echo $totalWaiting; ?>
                </div>
                <div>Waiting Today</div>
            </div>

            <div class="stat-box">
                <div class="number" id="countSales">
                    ₱<?php echo number_format($totalSales, 2); ?>
                </div>
                <div>Sales Today</div>
            </div>

            <?php if ($isAdmin) { ?>
                <div class="stat-box">
                    <div class="number" id="countAllSales">
                        ₱<?php echo number_format($allTimeSales, 2); ?>
                    </div>
                    <div>All-Time Sales (Admin)</div>
                </div>
            <?php } ?>

        </div>

        <div class="card" style="margin-top: 20px;">

            <h2>Services & Prices</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th>Price</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php while ($service = mysqli_fetch_assoc($services)) { ?>
                            <tr>
                                <td><?php echo $service['service_name']; ?></td>
                                <td>
                                    ₱<?php echo number_format($service['price'], 2); ?>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

        </div>

    </div>

    <script src="storage.js?v=2"></script>
    <script>
        document.getElementById("todayDate").textContent = today();
    </script>

</body>

</html>