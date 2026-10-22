// barbers.js - add, view, edit, delete, search/filter barbers
requireLogin();

var form = document.getElementById("barberForm");
var idInput = document.getElementById("barberId");
var nameInput = document.getElementById("name");
var specialtyInput = document.getElementById("specialty");
var statusInput = document.getElementById("status");
var errorText = document.getElementById("error");
var searchInput = document.getElementById("search");
var filterStatus = document.getElementById("filterStatus");
var table = document.getElementById("barberTable");

// ---------- Show the barber list (with search + filter) ----------
function showBarbers() {
    var list = getData("barbers");
    var keyword = searchInput.value.toLowerCase();
    var statusFilter = filterStatus.value;
    var rows = "";

    for (var i = 0; i < list.length; i++) {
        var b = list[i];
        var matchName = b.name.toLowerCase().indexOf(keyword) !== -1;
        var matchStatus = statusFilter === "" || b.status === statusFilter;

        if (matchName && matchStatus) {
            rows += "<tr>" +
                "<td>" + b.id + "</td>" +
                "<td>" + esc(b.name) + "</td>" +
                "<td>" + esc(b.specialty) + "</td>" +
                "<td><span class='badge " + b.status.replace(/ /g, "") + "'>" + b.status + "</span></td>" +
                "<td>" +
                "<button class='small' onclick='editBarber(" + b.id + ")'>Edit</button> " +
                "<button class='small red' onclick='deleteBarber(" + b.id + ")'>Delete</button>" +
                "</td>" +
                "</tr>";
        }
    }

    if (rows === "") {
        rows = "<tr><td colspan='5'>No barbers found.</td></tr>";
    }
    table.innerHTML = rows;
}

// ---------- Validation ----------
function validate(name, specialty, id) {
    if (name === "") {
        return "Name is required.";
    }
    if (name.length < 2) {
        return "Name is too short.";
    }
    if (specialty === "") {
        return "Please select a specialty.";
    }

    // No duplicate names
    var list = getData("barbers");
    for (var i = 0; i < list.length; i++) {
        if (list[i].name.toLowerCase() === name.toLowerCase() && list[i].id !== id) {
            return "A barber with this name already exists.";
        }
    }
    return "";
}

// ---------- Add / Update ----------
form.addEventListener("submit", function (e) {
    e.preventDefault();

    var name = nameInput.value.trim();
    var specialty = specialtyInput.value;
    var status = statusInput.value;
    var id = idInput.value === "" ? 0 : Number(idInput.value);

    var message = validate(name, specialty, id);
    if (message !== "") {
        errorText.textContent = message;
        return;
    }

    var list = getData("barbers");

    if (id === 0) {
        // ADD
        list.push({ id: nextId(list), name: name, specialty: specialty, status: status });
    } else {
        // UPDATE
        var barber = findById(list, id);
        barber.name = name;
        barber.specialty = specialty;
        barber.status = status;
    }

    saveData("barbers", list);
    clearForm();
    showBarbers();
});

// ---------- Edit ----------
function editBarber(id) {
    var barber = findById(getData("barbers"), id);
    idInput.value = barber.id;
    nameInput.value = barber.name;
    specialtyInput.value = barber.specialty;
    statusInput.value = barber.status;
    document.getElementById("formTitle").textContent = "Edit Barber / Stylist";
    document.getElementById("saveBtn").textContent = "Update Barber";
    errorText.textContent = "";
    window.scrollTo(0, 0);
}

// ---------- Delete ----------
function deleteBarber(id) {
    if (!confirm("Delete this barber?")) {
        return;
    }
    var list = getData("barbers");
    var newList = [];
    for (var i = 0; i < list.length; i++) {
        if (list[i].id !== id) {
            newList.push(list[i]);
        }
    }
    saveData("barbers", newList);
    showBarbers();
}

// ---------- Clear form ----------
function clearForm() {
    form.reset();
    idInput.value = "";
    errorText.textContent = "";
    document.getElementById("formTitle").textContent = "Add Barber / Stylist";
    document.getElementById("saveBtn").textContent = "Save Barber";
}

document.getElementById("cancelBtn").addEventListener("click", clearForm);
searchInput.addEventListener("input", showBarbers);
filterStatus.addEventListener("change", showBarbers);

showBarbers();