
<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';




include "db_connect.php";

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM customers");

$row = mysqli_fetch_assoc($result);

echo "Total Customers: " . $row['total'];

?>