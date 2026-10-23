<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';

$id = (int) ($_GET['id'] ?? 0);

if ($id <= 0) {
    exit('Invalid queue ID.');
}

// Find the queue entry.
$result = mysqli_query($conn, "
    SELECT barber_id, status, paid
    FROM `queue`
    WHERE queue_id = $id
");

if (!$result) {
    exit('Unable to load the queue entry.');
}

$queue = mysqli_fetch_assoc($result);

if (!$queue) {
    echo "<script>
        alert('Queue entry not found.');
        window.location.href = 'queue.php';
    </script>";
    exit();
}

// Only unpaid Waiting entries can be started.
if ($queue['status'] !== 'Waiting' || $queue['paid'] == 1) {
    echo "<script>
        alert('Only unpaid Waiting entries can be started.');
        window.location.href = 'queue.php';
    </script>";
    exit();
}

$barber_id = (int) $queue['barber_id'];

// Check whether the barber is serving another customer.
$check = mysqli_query($conn, "
    SELECT queue_id
    FROM `queue`
    WHERE barber_id = $barber_id
    AND status = 'In Service'
    AND queue_id != $id
    LIMIT 1
");

if (!$check) {
    exit('Unable to check the barber queue.');
}

if (mysqli_num_rows($check) > 0) {
    echo "<script>
        alert('This barber is still serving another customer. Complete that service first.');
        window.location.href = 'queue.php';
    </script>";
    exit();
}

// Start the service.
$sql = "
    UPDATE `queue`
    SET status = 'In Service',
        start_time = CURTIME()
    WHERE queue_id = $id
    AND status = 'Waiting'
    AND paid = 0
";

if (mysqli_query($conn, $sql)) {
    header('Location: queue.php');
    exit();
} else {
    echo 'Error: ' . mysqli_error($conn);
}
?>