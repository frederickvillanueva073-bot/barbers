<?php
session_start();
include "db_connect.php";

$isAdmin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
$resultBarbers = mysqli_query($conn, "SELECT * FROM barbers");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barbers - Barbershop Queue System</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav>
        <span class="brand">Baldbershop ✄</span>
        <a href="home.php">Home</a>
        <a href="customer.php">Customers</a>
        <a href="barbers.php" class="active">Barbers</a>
        <a href="queue.php">Queue</a>
        <a href="payments.php">Payments</a>
                
        <form action="logout.php" method="POST"
            style="display: inline; margin: 0;">
            <button class="logout" type="submit">Logout</button>
        </form>

    </nav>

    <div class="container">
        <?php if ($isAdmin) { ?>
        <div class="card">
            <h2 id="formTitle">Add Barber / Stylist (Admin Only)</h2>
            <form action="add_barber.php" method="POST">
                <input type="hidden" id="barberId">

                <label for="first_name">First Name</label>
                <input type="text" name="first_name" placeholder="e.g. Mark" required>

                <label for="middle_name">Middle Name (optional)</label>
                <input type="text" name="middle_name" placeholder="e.g. Reyes">

                <label for="last_name">Last Name</label>
                <input type="text" name="last_name" placeholder="e.g. Santos" required>

                <label for="specialty">Specialty</label>
                <select name="specialty" id="specialty" required>
                    <option value="">-- Select specialty --</option>
                    <option value="Haircut">Haircut</option>
                    <option value="Shave">Shave</option>
                    <option value="Hair Color">Hair Color</option>
                    <option value="Hair Spa / Rebond">Hair Spa / Rebond</option>
                    <option value="All-around">All-around</option>
                </select>

                <label for="status">Status</label>
                <select name="status" id="status" required>
                    <option value="">-- Select status --</option>
                    <option value="Available">Available</option>
                    <option value="On Break">On Break</option>
                    <option value="Off Duty">Off Duty</option>
                </select>

                <p class="error" id="error"></p>
                <button type="submit" id="saveBtn">Save Barber</button>
                <button type="reset" class="gray" id="cancelBtn">Clear</button>
            </form>
        </div>
        <?php } ?>

        <div class="card">
            <h2>Barber / Stylist List</h2>
            <div class="toolbar">
                <input type="text" id="search" placeholder="Search by name...">
                <select id="filterStatus">
                    <option value="">All Status</option>
                    <option value="Available">Available</option>
                    <option value="On Break">On Break</option>
                    <option value="Off Duty">Off Duty</option>
                </select>
            </div>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Specialty</th>
                            <th>Status</th>
                            <?php if ($isAdmin) { ?>
                                <th>Actions</th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody id="barberTable">

                        <?php while($barber = mysqli_fetch_assoc($resultBarbers)) { ?>

                            <tr>
                                <td><?php echo $barber['barber_id']; ?></td>
                                <td>
                                    <?php echo $barber['first_name'] . ' ' . $barber['middle_name'] . ' ' . $barber['last_name']; ?>
                                </td>
                                <td><?php echo $barber['specialty']; ?></td>
                                <td><?php echo $barber['status']; ?></td>
                                <?php if ($isAdmin) { ?>
                                    <td>
                                        <a href="edit_barber.php?id=<?php echo $barber['barber_id']; ?>">Edit</a>
                                        <a href="delete_barber.php?id=<?php echo $barber['barber_id']; ?>">Delete</a>
                                    </td>
                                <?php } ?>
                            </tr>

                        <?php } ?>

                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="storage.js?v=2"></script>
    <script>
        function filterBarbers() {
            var keyword = document.getElementById("search").value.toLowerCase();
            var status = document.getElementById("filterStatus").value;
            var rows = document.querySelectorAll("#barberTable tr");

            rows.forEach(function (row) {
                var name = row.cells[1].textContent.toLowerCase();
                var rowStatus = row.cells[3].textContent.trim();

                var matchName = name.indexOf(keyword) !== -1;
                var matchStatus = status === "" || rowStatus === status;

                if (matchName && matchStatus) {
                    row.style.display = "";
                } else {
                    row.style.display = "none";
                }
            });
        }

        document.getElementById("search").addEventListener("input", filterBarbers);
        document.getElementById("filterStatus").addEventListener("change", filterBarbers);
    </script>
</body>

</html>