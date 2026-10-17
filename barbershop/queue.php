<?php
include "db_connect.php";

$resultQueue = mysqli_query($conn, "

SELECT 
queue.*,
customers.name AS customer_name,
barbers.name AS barber_name,
services.service_name,
services.price

FROM queue

JOIN customers 
ON queue.customer_id = customers.customer_id

JOIN barbers
ON queue.barber_id = barbers.barber_id

JOIN services
ON queue.service_id = services.service_id

");
$customers = mysqli_query($conn, "SELECT * FROM customers");

$barbers = mysqli_query($conn, "SELECT * FROM barbers");

$services = mysqli_query($conn, "SELECT * FROM services");

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue - Barbershop Queue System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav>
        <span class="brand">Barbershop</span>
         <a href="home.php" >Home</a>
            <a href="customer.php">Customers</a>
            <a href="barbers.php">Barbers</a>
            <a href="queue.php"class="active">Queue</a>
            <a href="payments.php">Payments</a>
        <button class="logout" onclick="logout()">Logout</button>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Add to Queue / Appointment</h2>
           <form action="add_queue.php" method="POST">
                <label for="customer">Customer</label>
               <select name="customer_id" id="customer" required>
                    <option value="">
                            -- Select customer --
                     </option>

                    <?php while($customer = mysqli_fetch_assoc($customers)) { ?>

                        <option value="<?php echo $customer['customer_id']; ?>">
                            <?php echo $customer['name']; ?>
                        </option>

                    <?php } ?>

                </select>

                <label for="barber">Barber / Stylist (available only)</label>
              <select name="barber_id" id="barber" required>

                    <option value=""required>
                        -- Select barber --
                    </option>

                    <?php while($barber = mysqli_fetch_assoc($barbers)) { ?>

                        <option value="<?php echo $barber['barber_id']; ?>">
                            <?php echo $barber['name']; ?>
                        </option>

                    <?php } ?>

                </select>

               <label for="service">Service</label>
                <select name="service_id" id="service" required>

                    <option value="" required>
                        -- Select service --
                    </option>

                    <?php while($service = mysqli_fetch_assoc($services)) { ?>

                     <option 
                        value="<?php echo $service['service_id']; ?>"
                        data-price="<?php echo $service['price']; ?>">
                        
                        <?php echo $service['service_name']; ?>

                    </option>
                    <?php } ?>

                </select>

                <label>Price</label>
                <input type="text" name="price" id="price" readonly>

                <p class="error" id="error"></p>
                <button type="submit">Get Queue Number</button>
            </form>
        </div>

        <div class="card">
            <h2>Queue List</h2>
            <div class="toolbar">
                <input type="text" id="search" placeholder="Search customer, barber, or queue no...">
                <select id="filterStatus">
                    <option value="">All Status</option>
                    <option value="Waiting">Waiting</option>
                    <option value="In Service">In Service</option>
                    <option value="Done">Done</option>
                </select>
                <input type="date" id="filterDate">
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Queue No.</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Barber</th>
                            <th>Service</th>
                            <th>Price</th>
                            <th>Start</th>
                            <th>End</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
         <tbody>

                <?php while($queue = mysqli_fetch_assoc($resultQueue)) { ?>

                <tr>

                    <td><?php echo $queue['queue_number']; ?></td>

                    <td><?php echo $queue['date']; ?></td>

                    <td><?php echo $queue['customer_name']; ?></td>

                    <td><?php echo $queue['barber_name']; ?></td>

                    <td><?php echo $queue['service_name']; ?></td>

                    <td><?php echo $queue['price']; ?></td>

                   <td>
                        <?php echo $queue['start_time'] ? $queue['start_time'] : "-"; ?>
                    </td>

                    <td>
                        <?php echo $queue['end_time'] ? $queue['end_time'] : "-"; ?>
                    </td>

                    <td><?php echo $queue['status']; ?></td>

                <td>

                    <?php if($queue['status'] == "Waiting"){ ?>

                        <a href="start_service.php?id=<?php echo $queue['queue_id']; ?>">
                            Start Service
                        </a>

                    <?php } elseif($queue['status'] == "In Service"){ ?>

                        <a href="complete_service.php?id=<?php echo $queue['queue_id']; ?>">
                            Complete
                        </a>

                    <?php } else { ?>

                        Completed

                    <?php } ?>


                    |

                    <a href="edit_queue.php?id=<?php echo $queue['queue_id']; ?>">
                        Edit
                    </a>

                    |

                    <a href="delete_queue.php?id=<?php echo $queue['queue_id']; ?>"
                    onclick="return confirm('Are you sure you want to delete this queue?');">
                        Delete
                    </a>


                </td>

                </tr>

                <?php } ?>

      </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="storage.js"></script>
    <script>

document.getElementById("service").addEventListener("change", function(){

    let price = this.options[this.selectedIndex].getAttribute("data-price");

    document.getElementById("price").value = price;

});

</script>
</body>

</html>