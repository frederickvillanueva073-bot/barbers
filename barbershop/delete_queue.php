
<?php
require_once 'auth.php';
requireLogin();
require_once 'db_connect.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id || $id <= 0) {
    header('Location: queue.php');
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT queue_id FROM `queue` WHERE queue_id = ?'
);

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$queueExists = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if (!$queueExists) {
    header('Location: queue.php');
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    'SELECT payment_id FROM payments WHERE queue_id = ? LIMIT 1'
);

mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);

$hasPayment = mysqli_stmt_num_rows($stmt) > 0;
mysqli_stmt_close($stmt);

if ($hasPayment) {
    echo '<script>
        alert("Cannot delete this queue because it already has a payment record.");
        window.location.href = "queue.php";
    </script>';
    exit();
}

$stmt = mysqli_prepare(
    $conn,
    'DELETE FROM `queue` WHERE queue_id = ?'
);

mysqli_stmt_bind_param($stmt, 'i', $id);
$success = mysqli_stmt_execute($stmt);
mysqli_stmt_close($stmt);

if ($success) {
    header('Location: queue.php');
    exit();
}

http_response_code(500);
echo 'Unable to delete the queue entry.';
?>
