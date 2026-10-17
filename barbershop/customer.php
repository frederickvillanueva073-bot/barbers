<?php
include "db_connect.php";
$resultCustomers = mysqli_query($conn, "SELECT * FROM customers");
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customers - Barbershop Queue System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav>
        <span class="brand">Barbershop</span>
            <a href="home.php" >Home</a>
            <a href="customer.php "class="active">Customers</a>
            <a href="barbers.php">Barbers</a>
            <a href="queue.php">Queue</a>
            
            
            <a href="payments.php">Payments</a>
        <button class="logout" onclick="logout()">Logout</button>
    </nav>

    <div class="container">
        <div class="card">
            <h2 id="formTitle">Add Customer</h2>
            <form action="add_customer.php" method="POST">
                <input type="hidden" id="customerId">

                <label for="name">Full Name</label>
                <input type="text" name="name" placeholder="e.g. Juan Dela Cruz" required>

                <label for="phone">Phone Number</label>
                <input type="text" name="phone" placeholder="e.g. 09171234567" required>

                <label for="email">Email (optional)</label>
                <input type="text" name="email" placeholder="e.g. juan@email.com">

                <p class="error" id="error"></p>
                <button type="submit" id="saveBtn">Save Customer</button>
                <button type="button" class="gray" id="cancelBtn">Clear</button>
            </form>
        </div>

        <div class="card">
            <h2>Customer List</h2>
            <div class="toolbar">
                <input type="text" id="search" placeholder="Search by name or phone...">
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Email</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($customer = mysqli_fetch_assoc($resultCustomers)) { ?>

                        <tr>
                            <td><?php echo $customer['customer_id']; ?></td>
                            <td><?php echo $customer['name']; ?></td>
                            <td><?php echo $customer['phone']; ?></td>
                            <td><?php echo $customer['email']; ?></td>
                            <td>
                                <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>">
                                        Edit
                                    </a>

                                
                             <a href="delete_customer.php?id=<?php echo $customer['customer_id']; ?>">
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
    
</body>

</html>