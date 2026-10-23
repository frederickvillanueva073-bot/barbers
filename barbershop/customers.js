// customers.js - add, view, edit, delete, search customers
requireLogin();

var form = document.getElementById("customerForm");
var idInput = document.getElementById("customerId");
var nameInput = document.getElementById("name");
var phoneInput = document.getElementById("phone");
var emailInput = document.getElementById("email");
var errorText = document.getElementById("error");
var searchInput = document.getElementById("search");
var table = document.getElementById("customerTable");

// ---------- Show the customer list ----------
function showCustomers() {
    var list = getData("customers");
    var keyword = searchInput.value.toLowerCase();
    var rows = "";

    for (var i = 0; i < list.length; i++) {
        var c = list[i];
        var match = c.name.toLowerCase().indexOf(keyword) !== -1 ||
            c.phone.indexOf(keyword) !== -1;

        if (match) {
            rows += "<tr>" +
                "<td>" + c.id + "</td>" +
                "<td>" + esc(c.name) + "</td>" +
                "<td>" + esc(c.phone) + "</td>" +
                "<td>" + esc(c.email) + "</td>" +
                "<td>" +
                "<button class='small' onclick='editCustomer(" + c.id + ")'>Edit</button> " +
                "<button class='small red' onclick='deleteCustomer(" + c.id + ")'>Delete</button>" +
                "</td>" +
                "</tr>";
        }
    }

    if (rows === "") {
        rows = "<tr><td colspan='5'>No customers found.</td></tr>";
    }
    table.innerHTML = rows;
}

// ---------- Validation ----------
function validate(name, phone, email, id) {
    if (name === "") {
        return "Name is required.";
    }
    if (name.length < 2) {
        return "Name is too short.";
    }
    if (phone === "") {
        return "Phone number is required.";
    }
    // Philippine mobile number: starts with 09 and has 11 digits
    if (!/^09[0-9]{9}$/.test(phone)) {
        return "Phone must be 11 digits and start with 09 (e.g. 09171234567).";
    }
    if (email !== "" && !/^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(email)) {
        return "Email format is not valid.";
    }

    // No duplicate phone numbers
    var list = getData("customers");
    for (var i = 0; i < list.length; i++) {
        if (list[i].phone === phone && list[i].id !== id) {
            return "This phone number is already registered.";
        }
    }
    return "";
}

// ---------- Add / Update ----------
form.addEventListener("submit", function (e) {
    e.preventDefault();

    var name = nameInput.value.trim();
    var phone = phoneInput.value.trim();
    var email = emailInput.value.trim();
    var id = idInput.value === "" ? 0 : Number(idInput.value);

    var message = validate(name, phone, email, id);
    if (message !== "") {
        errorText.textContent = message;
        return;
    }

    var list = getData("customers");

    if (id === 0) {
        // ADD
        list.push({ id: nextId(list), name: name, phone: phone, email: email });
    } else {
        // UPDATE
        var customer = findById(list, id);
        customer.name = name;
        customer.phone = phone;
        customer.email = email;
    }

    saveData("customers", list);
    clearForm();
    showCustomers();
});

// ---------- Edit ----------
function editCustomer(id) {
    var customer = findById(getData("customers"), id);
    idInput.value = customer.id;
    nameInput.value = customer.name;
    phoneInput.value = customer.phone;
    emailInput.value = customer.email;
    document.getElementById("formTitle").textContent = "Edit Customer";
    document.getElementById("saveBtn").textContent = "Update Customer";
    errorText.textContent = "";
    window.scrollTo(0, 0);
}

// ---------- Delete ----------
function deleteCustomer(id) {
    if (!confirm("Delete this customer?")) {
        return;
    }
    var list = getData("customers");
    var newList = [];
    for (var i = 0; i < list.length; i++) {
        if (list[i].id !== id) {
            newList.push(list[i]);
        }
    }
    saveData("customers", newList);
    showCustomers();
}

// ---------- Clear form ----------
function clearForm() {
    form.reset();
    idInput.value = "";
    errorText.textContent = "";
    document.getElementById("formTitle").textContent = "Add Customer";
    document.getElementById("saveBtn").textContent = "Save Customer";
}

document.getElementById("cancelBtn").addEventListener("click", clearForm);
searchInput.addEventListener("input", showCustomers);

showCustomers();