<?php

include "db_connect.php";


$queue_id = $_POST['queue_id'];
$payment_method = $_POST['payment_method'];


// get service price

$result = mysqli_query($conn, "

SELECT services.price

FROM queue

JOIN services

ON queue.service_id = services.service_id

WHERE queue.queue_id='$queue_id'

");


$row = mysqli_fetch_assoc($result);


$total = $row['price'];

$amount_paid = $total;

$change_amount = 0;



// insert payment

$sql = "

INSERT INTO payments


(queue_id, total, amount_paid, change_amount, payment_method)

VALUES

('$queue_id',
'$total',
'$amount_paid',
'$change_amount',
'$payment_method')

";

mysqli_query($conn, "
UPDATE queue
SET paid = 1
WHERE queue_id = '$queue_id'
");

if(mysqli_query($conn,$sql)){


    // update queue paid status

    mysqli_query($conn, "

    UPDATE queue

    SET paid=1

    WHERE queue_id='$queue_id'

    ");



    header("Location: payments.php");


}else{


    echo "Error: ".mysqli_error($conn);


}

?>