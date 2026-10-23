// ---------- Queue page ----------
requireLogin();

var form = document.getElementById("queueForm");
var customerSelect = document.getElementById("customer");
var barberSelect = document.getElementById("barber");
var serviceSelect = document.getElementById("service");
var priceInput = document.getElementById("price");
var errorText = document.getElementById("error");
var searchInput = document.getElementById("search");
var filterStatus = document.getElementById("filterStatus");
var filterDate = document.getElementById("filterDate");
var table = document.getElementById("queueTable");

// Show today's queue by default
filterDate.value = today();

// ---------- Fill the dropdowns ----------
function fillDropdowns() {
    var customers = getData("customers");
    var barbers = getData("barbers");

    customerSelect.innerHTML = "<option value=''>-- Select customer --</option>";
    for (var i = 0; i < customers.length; i++) {
        customerSelect.innerHTML += "<option value='" + customers[i].id + "'>" +
            esc(customers[i].name) + "</option>";
    }

    barberSelect.innerHTML = "<option value=''>-- Select barber --</option>";
    for (var j = 0; j < barbers.length; j++) {
        if (barbers[j].status === "Available") {
            barberSelect.innerHTML += "<option value='" + barbers[j].id + "'>" +
                esc(barbers[j].name) + " (" + esc(barbers[j].specialty) + ")</option>";
        }
    }

    serviceSelect.innerHTML = "<option value=''>-- Select service --</option>";
    for (var k = 0; k < SERVICES.length; k++) {
        serviceSelect.innerHTML += "<option value='" + SERVICES[k].name + "'>" +
            SERVICES[k].name + "</option>";
    }
}

// Show price when a service is picked
serviceSelect.addEventListener("change", function () {
    if (serviceSelect.value === "") {
        priceInput.value = "";
    } else {
        priceInput.value = peso(getServicePrice(serviceSelect.value));
    }
});

// ---------- Make the next queue number for today ----------
function makeQueueNumber() {
    var queue = getData("queue");
    var count = 0;
    for (var i = 0; i < queue.length; i++) {
        if (queue[i].date === today()) {
            count++;
        }
    }
    return "Q-" + String(count + 1).padStart(3, "0");
}

// ---------- Add to queue ----------
form.addEventListener("submit", function (e) {
    e.preventDefault();

    if (customerSelect.value === "") {
        errorText.textContent = "Please select a customer.";
        return;
    }
    if (barberSelect.value === "") {
        errorText.textContent = "Please select a barber (add one in the Barbers page if empty).";
        return;
    }
    if (serviceSelect.value === "") {
        errorText.textContent = "Please select a service.";
        return;
    }

    var customer = findById(getData("customers"), Number(customerSelect.value));
    var barber = findById(getData("barbers"), Number(barberSelect.value));
    var queue = getData("queue");

    queue.push({
        id: nextId(queue),
        queueNo: makeQueueNumber(),
        date: today(),
        customerName: customer.name,
        barberName: barber.name,
        barberId: barber.id,
        service: serviceSelect.value,
        price: getServicePrice(serviceSelect.value),
        startTime: "",
        endTime: "",
        status: "Waiting",
        paid: false
    });

    saveData("queue", queue);
    form.reset();
    priceInput.value = "";
    errorText.textContent = "";
    showQueue();
});

// ---------- Show the queue (with search + filters) ----------
function showQueue() {
    var queue = getData("queue");
    var keyword = searchInput.value.toLowerCase();
    var statusFilter = filterStatus.value;
    var dateFilter = filterDate.value;
    var rows = "";

    for (var i = 0; i < queue.length; i++) {
        var q = queue[i];

        var text = (q.queueNo + " " + q.customerName + " " + q.barberName).toLowerCase();
        var matchText = text.indexOf(keyword) !== -1;
        var matchStatus = statusFilter === "" || q.status === statusFilter;
        var matchDate = dateFilter === "" || q.date === dateFilter;

        if (matchText && matchStatus && matchDate) {
            var button = "";
            if (q.status === "Waiting") {
                button = "<button class='small green' onclick='startService(" + q.id + ")'>Start</button>";
            } else if (q.status === "In Service") {
                button = "<button class='small' onclick='finishService(" + q.id + ")'>Finish</button>";
            } else if (q.paid) {
                button = "Paid";
            } else {
                button = "Unpaid";
            }

            rows += "<tr>" +
                "<td>" + q.queueNo + "</td>" +
                "<td>" + q.date + "</td>" +
                "<td>" + esc(q.customerName) + "</td>" +
                "<td>" + esc(q.barberName) + "</td>" +
                "<td>" + esc(q.service) + "</td>" +
                "<td>" + peso(q.price) + "</td>" +
                "<td>" + (q.startTime || "-") + "</td>" +
                "<td>" + (q.endTime || "-") + "</td>" +
                "<td><span class='badge " + q.status.replace(/ /g, "") + "'>" + q.status + "</span></td>" +
                "<td>" + button + "</td>" +
                "</tr>";
        }
    }

    if (rows === "") {
        rows = "<tr><td colspan='10'>No queue records found.</td></tr>";
    }
    table.innerHTML = rows;
}

// ---------- Start service ----------
function startService(id) {
    var queue = getData("queue");
    var item = findById(queue, id);

    // A barber can only serve one customer at a time
    for (var i = 0; i < queue.length; i++) {
        if (queue[i].barberId === item.barberId && queue[i].status === "In Service") {
            alert(item.barberName + " is still serving another customer.");
            return;
        }
    }

    item.status = "In Service";
    item.startTime = nowTime();
    saveData("queue", queue);
    showQueue();
}

// ---------- Finish service ----------
function finishService(id) {
    var queue = getData("queue");
    var item = findById(queue, id);

    item.status = "Done";
    item.endTime = nowTime();
    saveData("queue", queue);
    showQueue();
}

searchInput.addEventListener("input", showQueue);
filterStatus.addEventListener("change", showQueue);
filterDate.addEventListener("change", showQueue);

fillDropdowns();
showQueue();