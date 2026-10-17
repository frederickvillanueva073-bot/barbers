<?php

include "db_connect.php";


$payments = mysqli_query($conn, "

SELECT 
payments.*,
queue.queue_number,
queue.date,
queue.paid,
customers.name AS customer_name,
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

");


$totalSales = mysqli_fetch_assoc($totalSalesQuery)['total_sales'];

if(!$totalSales){
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
</head>


<body>


<nav>

<span class="brand">Barbershop</span>

<a href="home.php">Home</a>
<a href="customer.php">Customers</a>
<a href="barbers.php">Barbers</a>
<a href="services.php">Services</a>
<a href="queue.php">Queue</a>
<a href="payments.php" class="active">Payments</a>

<button class="logout" onclick="logout()">Logout</button>

</nav>



<div class="container">


<div class="card">

<h2>Record Payment</h2>

<p>Only finished (Done) services that are not yet paid are shown.</p>


<form action="save_payment.php" method="POST">


<label>Queue Number</label>

<select name="queue_id">

<option value="">
-- Select Queue --
</option>


<?php

$pending = mysqli_query($conn, "

SELECT queue_id, queue_number

FROM queue

WHERE status='Done'
AND paid=0

");


while ($row = mysqli_fetch_assoc($pending)) {

?>

<option value="<?php echo $row['queue_id']; ?>">

<?php echo $row['queue_number']; ?>

</option>


<?php } ?>


</select>



<label>Payment Method</label>

<select name="payment_method">

<option value="Cash">Cash</option>
<option value="GCash">GCash</option>
<option value="Card">Card</option>

</select>



<button type="submit">
Save Payment
</button>


</form>


</div>





<div class="card">


<h2>Payment History</h2>


<div class="toolbar">

<input type="text" placeholder="Search customer or queue no...">

<select>

<option>All Methods</option>
<option>Cash</option>
<option>GCash</option>
<option>Card</option>

</select>


<input type="date">


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



<tbody>


<?php while ($payment = mysqli_fetch_assoc($payments)) { ?>


<tr>


<td>

<?php echo $payment['payment_id']; ?>

</td>



<td>

<?php echo $payment['date']; ?>

</td>



<td>

<?php echo $payment['queue_number']; ?>

</td>




<td>

<?php echo $payment['customer_name']; ?>

</td>



<td>

<?php echo $payment['service_name']; ?>

</td>



<td>

₱<?php echo $payment['price']; ?>

</td>



<td>

₱<?php echo $payment['amount_paid']; ?>

</td>



<td>

₱<?php echo $payment['change_amount']; ?>

</td>



<td>


<?php

if ($payment['paid'] == 1) {

echo "Paid";

} else {

echo "Unpaid";

}

?>


</td>



<td>

<?php echo $payment['payment_method']; ?>

</td>




<td>

Completed

</td>


</tr>


<?php } ?>


</tbody>



</table>


</div>



<h3>

Total Sales Today

₱<?php echo number_format($totalSales,2); ?>

</h3>


</div>


</div>




<script src="storage.js"></script>


</body>

</html>