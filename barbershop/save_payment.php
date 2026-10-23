
<?php
require_once 'auth.php';
requireLogin();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: payments.php');
    exit();
}

$queue_id = filter_input(INPUT_POST, 'queue_id', FILTER_VALIDATE_INT);
$amount_paid = $_POST['amount_paid'] ?? '';
$payment_method = $_POST['payment_method'] ?? '';

if (
    !$queue_id ||
    !is_numeric($amount_paid) ||
    !is_finite((float) $amount_paid) ||
    (float) $amount_paid < 0 ||
    !in_array($payment_method, ['Cash', 'GCash', 'Card'], true)
) {
    exit('Invalid payment information.');
}

$amount_paid = round((float) $amount_paid, 2);

mysqli_begin_transaction($conn);

try {
    $stmt = mysqli_prepare(
        $conn,
        "SELECT status, paid
         FROM `queue`
         WHERE queue_id = ?
         FOR UPDATE"
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $queue_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $status, $paid);

    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception('Queue entry not found.');
    }

    mysqli_stmt_close($stmt);

    if ($status !== 'Done') {
        throw new Exception('Only completed services can be paid.');
    }

    if ((int) $paid === 1) {
        throw new Exception('This queue entry has already been paid.');
    }

    $stmt = mysqli_prepare(
        $conn,
        "SELECT COALESCE(SUM(s.price), 0)
         FROM queue_services qs
         JOIN services s ON s.service_id = qs.service_id
         WHERE qs.queue_id = ?"
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $queue_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $junction_total);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    if ((float) $junction_total > 0) {
        $total = round((float) $junction_total, 2);
    } else {
        $stmt = mysqli_prepare(
            $conn,
            "SELECT COALESCE(s.price, 0)
             FROM `queue` q
             LEFT JOIN services s ON s.service_id = q.service_id
             WHERE q.queue_id = ?"
        );

        if (!$stmt) {
            throw new Exception(mysqli_error($conn));
        }

        mysqli_stmt_bind_param($stmt, 'i', $queue_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $legacy_total);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);

        $total = round((float) $legacy_total, 2);
    }

    if ($total <= 0) {
        throw new Exception('Unable to determine the service price.');
    }

    if ($amount_paid < $total) {
        throw new Exception(
            'Insufficient payment. Total due: ₱' .
            number_format($total, 2)
        );
    }

    $change_amount = round($amount_paid - $total, 2);

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO payments
        (queue_id, total, amount_paid, change_amount, payment_method, payment_date)
        VALUES (?, ?, ?, ?, ?, CURDATE())"
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iddds',
        $queue_id,
        $total,
        $amount_paid,
        $change_amount,
        $payment_method
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare(
        $conn,
        "UPDATE `queue`
         SET paid = 1
         WHERE queue_id = ? AND paid = 0"
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, 'i', $queue_id);

    if (!mysqli_stmt_execute($stmt) || mysqli_stmt_affected_rows($stmt) !== 1) {
        throw new Exception('Unable to mark the queue as paid.');
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    header('Location: payments.php');
    exit();

} catch (Throwable $e) {
    mysqli_rollback($conn);
    error_log('Payment processing failed: ' . $e->getMessage());

    http_response_code(400);
    exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>
