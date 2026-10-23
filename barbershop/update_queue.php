<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: queue.php');
    exit();
}

$queue_id = filter_input(INPUT_POST, 'queue_id', FILTER_VALIDATE_INT);
$customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
$barber_id = filter_input(INPUT_POST, 'barber_id', FILTER_VALIDATE_INT);
$service_ids = $_POST['service_ids'] ?? [];
$status = $_POST['status'] ?? '';

$allowedStatuses = ['Waiting', 'In Service', 'Done'];

if (
    !$queue_id ||
    !$customer_id ||
    !$barber_id ||
    !is_array($service_ids) ||
    empty($service_ids) ||
    !in_array($status, $allowedStatuses, true)
) {
    exit('Invalid queue information. Please check your selections.');
}

$service_ids = array_map('intval', $service_ids);
$service_ids = array_values(array_unique($service_ids));

foreach ($service_ids as $service_id) {
    if ($service_id <= 0) {
        exit('Invalid service selection.');
    }
}

// Check that the customer exists.
$check = mysqli_prepare(
    $conn,
    'SELECT customer_id FROM customers WHERE customer_id = ?'
);

mysqli_stmt_bind_param($check, 'i', $customer_id);
mysqli_stmt_execute($check);
mysqli_stmt_store_result($check);

$customerExists = mysqli_stmt_num_rows($check) > 0;

mysqli_stmt_close($check);

if (!$customerExists) {
    exit('The selected customer does not exist.');
}

// Check that each selected service exists.
$check = mysqli_prepare(
    $conn,
    'SELECT service_id FROM services WHERE service_id = ?'
);

foreach ($service_ids as $service_id) {
    mysqli_stmt_bind_param($check, 'i', $service_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_store_result($check);

    if (mysqli_stmt_num_rows($check) === 0) {
        mysqli_stmt_close($check);
        exit('One of the selected services does not exist.');
    }

    mysqli_stmt_free_result($check);
}

mysqli_stmt_close($check);

mysqli_begin_transaction($conn);

try {
    // Get the saved queue information.
    $stmt = mysqli_prepare(
        $conn,
        'SELECT barber_id, status, start_time, paid
         FROM `queue`
         WHERE queue_id = ?
         FOR UPDATE'
    );

    mysqli_stmt_bind_param($stmt, 'i', $queue_id);
    mysqli_stmt_execute($stmt);

    mysqli_stmt_bind_result(
        $stmt,
        $old_barber_id,
        $old_status,
        $start_time,
        $paid
    );

    if (!mysqli_stmt_fetch($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception('Queue entry not found.');
    }

    mysqli_stmt_close($stmt);

    if ((int) $paid === 1) {
        mysqli_rollback($conn);

        echo "<script>
            alert('This queue entry has already been paid and cannot be edited.');
            window.location.href = 'queue.php';
        </script>";
        exit();
    }

    // Read the selected barber's current working status.
    $check = mysqli_prepare(
        $conn,
        'SELECT status FROM barbers WHERE barber_id = ?'
    );

    mysqli_stmt_bind_param($check, 'i', $barber_id);
    mysqli_stmt_execute($check);
    mysqli_stmt_bind_result($check, $barber_status);

    $barberExists = mysqli_stmt_fetch($check);

    mysqli_stmt_close($check);

    if (!$barberExists) {
        throw new Exception('The selected barber does not exist.');
    }

    // A completed entry may keep its original barber.
    $keepCompletedBarber = false;

    if (
        $old_status === 'Done' &&
        $status === 'Done' &&
        $old_barber_id == $barber_id
    ) {
        $keepCompletedBarber = true;
    }

    // All other assignments require an Available barber.
    if ($barber_status !== 'Available' && !$keepCompletedBarber) {
        mysqli_rollback($conn);

        echo "<script>
            alert('This barber is not Available. Please select an Available barber.');
            window.location.href = 'queue.php';
        </script>";
        exit();
    }

    // Prevent two customers being In Service with the same barber.
    if ($status === 'In Service') {
        $check = mysqli_query($conn, "
            SELECT queue_id
            FROM `queue`
            WHERE barber_id = $barber_id
            AND status = 'In Service'
            AND queue_id != $queue_id
            LIMIT 1
        ");

        if (!$check) {
            throw new Exception('Unable to check the barber queue.');
        }

        if (mysqli_num_rows($check) > 0) {
            mysqli_rollback($conn);

            echo "<script>
                alert('This barber is still serving another customer. Complete that service first.');
                window.location.href = 'queue.php';
            </script>";
            exit();
        }
    }

    $end_time = null;

    if ($status === 'In Service' && empty($start_time)) {
        $start_time = date('H:i:s');
    }

    if ($status === 'Done') {
        $end_time = date('H:i:s');
    }

    if ($status === 'Waiting') {
        $start_time = null;
    }

    $first_service_id = $service_ids[0];

    $stmt = mysqli_prepare(
        $conn,
        'UPDATE `queue`
         SET customer_id = ?,
             barber_id = ?,
             service_id = ?,
             status = ?,
             start_time = ?,
             end_time = ?
         WHERE queue_id = ?'
    );

    mysqli_stmt_bind_param(
        $stmt,
        'iiisssi',
        $customer_id,
        $barber_id,
        $first_service_id,
        $status,
        $start_time,
        $end_time,
        $queue_id
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare(
        $conn,
        'DELETE FROM queue_services WHERE queue_id = ?'
    );

    mysqli_stmt_bind_param($stmt, 'i', $queue_id);

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO queue_services (queue_id, service_id)
         VALUES (?, ?)'
    );

    foreach ($service_ids as $service_id) {
        mysqli_stmt_bind_param(
            $stmt,
            'ii',
            $queue_id,
            $service_id
        );

        if (!mysqli_stmt_execute($stmt)) {
            throw new Exception(mysqli_stmt_error($stmt));
        }
    }

    mysqli_stmt_close($stmt);

    mysqli_commit($conn);

    header('Location: queue.php');
    exit();

} catch (Throwable $e) {
    mysqli_rollback($conn);

    error_log('Queue update failed: ' . $e->getMessage());

    http_response_code(400);
    exit(htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}
?>