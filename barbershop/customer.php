
<?php
require_once 'auth.php';
requireLogin();

require_once 'db_connect.php';




$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
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
        <span class="brand">Baldbershop ✄</span>
        <a href="home.php">Home</a>
        <a href="customer.php" class="active">Customers</a>
        <a href="barbers.php">Barbers</a>
        <a href="queue.php">Queue</a>
        <a href="payments.php">Payments</a>
        
        <form action="logout.php" method="POST"
            style="display: inline; margin: 0;">
            <button class="logout" type="submit">Logout</button>
        </form>

    </nav>

    <div class="container">
        <div class="card">
            <h2 id="formTitle">Add Customer</h2>
            <!-- Added id="customerForm" here -->
            <form action="add_customer.php" method="POST" id="customerForm">
                <input type="hidden" id="customerId">

                <label for="first_name">First Name</label>
                <input type="text" name="first_name" placeholder="e.g. Juan" required>

                <label for="middle_name">Middle Name (optional)</label>
                <input type="text" name="middle_name" placeholder="e.g. Santos">

                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" placeholder="e.g. Dela Cruz" required>

                <label for="phone">Phone Number</label>
                <input type="tel" name="phone" id="phoneInput" placeholder="e.g. 09171234567" pattern="[0-9]*" inputmode="numeric" maxlength="11" required>

                <label for="email">Email (optional)</label>
                <input type="text" name="email" placeholder="e.g. juan@email.com">

                <p class="error" id="error"></p>
                <button type="submit" id="saveBtn">Save Customer</button>
                <button type="reset" class="gray" id="cancelBtn">Clear</button>
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
                    <tbody id="customerTable">
                        <?php while($customer = mysqli_fetch_assoc($resultCustomers)) { ?>
                        <tr>
                            <td><?php echo $customer['customer_id']; ?></td>
                            <td>
                                <?php echo $customer['first_name'] . ' ' . $customer['middle_name'] . ' ' . $customer['last_name']; ?>
                            </td>
                            <td><?php echo $customer['phone']; ?></td>
                            <td><?php echo $customer['email']; ?></td>
                            <td>
                                <a href="edit_customer.php?id=<?php echo $customer['customer_id']; ?>">Edit</a>
                                <?php if ($isAdmin) { ?>
                                    <a href="delete_customer.php?id=<?php echo $customer['customer_id']; ?>">Delete</a>
                                <?php } ?>
                            </td>
                        </tr>
                        <?php } ?>
                     </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="storage.js?v=2"></script>
    <script>
     
        document.getElementById("search").addEventListener("input", function () {
            var keyword = this.value.toLowerCase();
            var rows = document.querySelectorAll("#customerTable tr");

            rows.forEach(function (row) {
                var name = row.cells[1].textContent.toLowerCase();
                var phone = row.cells[2].textContent.toLowerCase();
                if (name.indexOf(keyword) !== -1 || phone.indexOf(keyword) !== -1) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        });

        // Form submit validation for phone number pop-up
        document.getElementById("customerForm").addEventListener("submit", function (e) {
            var phoneInput = document.getElementById("phoneInput").value;
            var phoneRegex = /^[0-9]+$/;
            
            if (!phoneRegex.test(phoneInput)) {
                e.preventDefault(); 
                alert('Invalid input! Phone numbers must contain numbers only.');
            }
        });
    </script>
</body>

</html>

