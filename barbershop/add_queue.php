
<?php
require_once 'auth.php';
requireLogin();
require_once 'db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: queue.php');
    exit();
}

$customer_id = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
$barber_id = filter_input(INPUT_POST, 'barber_id', FILTER_VALIDATE_INT);
$service_ids = $_POST['service_ids'] ?? [];

if (
    !$customer_id ||
    !$barber_id ||
    !is_array($service_ids) ||
    empty($service_ids)
) {
    exit('Please select a customer, barber, and at least one service.');
}

$service_ids = array_map('intval', $service_ids);
$service_ids = array_values(array_unique($service_ids));

foreach ($service_ids as $service_id) {
    if ($service_id <= 0) {
        exit('Invalid service selection.');
    }
}

$checkCustomer = mysqli_prepare(
    $conn,
    'SELECT customer_id FROM customers WHERE customer_id = ?'
);
mysqli_stmt_bind_param($checkCustomer, 'i', $customer_id);
mysqli_stmt_execute($checkCustomer);
mysqli_stmt_store_result($checkCustomer);
$customerExists = mysqli_stmt_num_rows($checkCustomer) > 0;
mysqli_stmt_close($checkCustomer);

$checkBarber = mysqli_prepare(
    $conn,
    'SELECT barber_id FROM barbers WHERE barber_id = ?'
);
mysqli_stmt_bind_param($checkBarber, 'i', $barber_id);
mysqli_stmt_execute($checkBarber);
mysqli_stmt_store_result($checkBarber);
$barberExists = mysqli_stmt_num_rows($checkBarber) > 0;
mysqli_stmt_close($checkBarber);

if (!$customerExists || !$barberExists) {
    exit('The selected customer or barber does not exist.');
}

$checkService = mysqli_prepare(
    $conn,
    'SELECT service_id FROM services WHERE service_id = ?'
);

foreach ($service_ids as $service_id) {
    mysqli_stmt_bind_param($checkService, 'i', $service_id);
    mysqli_stmt_execute($checkService);
    mysqli_stmt_store_result($checkService);

    if (mysqli_stmt_num_rows($checkService) === 0) {
        mysqli_stmt_close($checkService);
        exit('One of the selected services does not exist.');
    }

    mysqli_stmt_free_result($checkService);
}

mysqli_stmt_close($checkService);

mysqli_begin_transaction($conn);

try {
    $result = mysqli_query(
        $conn,
        "SELECT COALESCE(MAX(queue_id), 0) + 1 AS next_number
         FROM `queue`"
    );

    if (!$result) {
        throw new Exception(mysqli_error($conn));
    }

    $row = mysqli_fetch_assoc($result);
    $queue_number = 'Q-' . str_pad(
        $row['next_number'],
        3,
        '0',
        STR_PAD_LEFT
    );

    $first_service_id = $service_ids[0];

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO `queue`
        (customer_id, barber_id, service_id, queue_number, `date`, status, paid)
        VALUES (?, ?, ?, ?, CURDATE(), 'Waiting', 0)"
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

    mysqli_stmt_bind_param(
        $stmt,
        'iiis',
        $customer_id,
        $barber_id,
        $first_service_id,
        $queue_number
    );

    if (!mysqli_stmt_execute($stmt)) {
        throw new Exception(mysqli_stmt_error($stmt));
    }

    $queue_id = mysqli_insert_id($conn);
    mysqli_stmt_close($stmt);

    $stmt = mysqli_prepare(
        $conn,
        'INSERT INTO queue_services (queue_id, service_id)
         VALUES (?, ?)'
    );

    if (!$stmt) {
        throw new Exception(mysqli_error($conn));
    }

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
    error_log('Queue creation failed: ' . $e->getMessage());

    http_response_code(500);
    exit('Unable to create the queue entry. Check the PHP error log.');
}
?>
