<?php
include "db_connect.php";

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
        <span class="brand">Barbershop</span>
       <a href="home.php" >Home</a>
            <a href="customer.php">Customers</a>
            <a href="barbers.php"class="active">Barbers</a>
            <a href="queue.php">Queue</a>
            <a href="payments.php">Payments</a>
        <button class="logout" onclick="logout()">Logout</button>
    </nav>

    <div class="container">
        <div class="card">
            <h2 id="formTitle">Add Barber / Stylist</h2>
            <form action="add_barber.php" method="POST">
                <input type="hidden" id="barberId">

                <label for="name">Full Name</label>
               <input type="text" name="name" placeholder="e.g. Mark Santos" required>

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
                <select name="status" id="status"required>
                     <option value="">-- Select status --</option>
                    <option value="Available">Available</option>
                    <option value="On Break">On Break</option>
                    <option value="Off Duty">Off Duty</option>
                </select>

                <p class="error" id="error"></p>
                <button type="submit" id="saveBtn">Save Barber</button>
                <button type="button" class="gray" id="cancelBtn">Clear</button>
            </form>
        </div>

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
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php while($barber = mysqli_fetch_assoc($resultBarbers)) { ?>

                            <tr>
                                <td><?php echo $barber['barber_id']; ?></td>
                                <td><?php echo $barber['name']; ?></td>
                                <td><?php echo $barber['specialty']; ?></td>
                                <td><?php echo $barber['status']; ?></td>
                                  <td>
                                        <a href="edit_barber.php?id=<?php echo $barber['barber_id']; ?>">
                                            Edit
                                        </a>
                                            <a href="delete_barber.php?id=<?php echo $barber['barber_id']; ?>">
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