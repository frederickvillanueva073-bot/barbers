// ---------- Payments page ----------
requireLogin();

var form = document.getElementById("paymentForm");
var queueSelect = document.getElementById("queueItem");
var totalInput = document.getElementById("total");
var methodInput = document.getElementById("method");
var amountInput = document.getElementById("amountPaid");
var errorText = document.getElementById("error");
var messageText = document.getElementById("message");
var searchInput = document.getElementById("search");
var filterMethod = document.getElementById("filterMethod");
var filterDate = document.getElementById("filterDate");
var table = document.getElementById("paymentTable");

// ---------- Fill the dropdown with finished + unpaid services ----------
function fillQueueDropdown() {
    var queue = getData("queue");
    queueSelect.innerHTML = "<option value=''>-- Select finished service --</option>";

    for (var i = 0; i < queue.length; i++) {
        var q = queue[i];
        if (q.status === "Done" && !q.paid) {
            queueSelect.innerHTML += "<option value='" + q.id + "'>" +
                q.queueNo + " (" + q.date + ") - " + esc(q.customerName) + " - " +
                esc(q.service) + "</option>";
        }
    }
    totalInput.value = "";
}

queueSelect.addEventListener("change", function () {
    if (queueSelect.value === "") {
        totalInput.value = "";
    } else {
        var item = findById(getData("queue"), Number(queueSelect.value));
        totalInput.value = peso(item.price);
    }
});

// ---------- Save payment ----------
form.addEventListener("submit", function (e) {
    e.preventDefault();
    messageText.textContent = "";

    if (queueSelect.value === "") {
        errorText.textContent = "Please select a finished service.";
        return;
    }
    if (methodInput.value === "") {
        errorText.textContent = "Please select a payment method.";
        return;
    }
    if (amountInput.value === "" || Number(amountInput.value) <= 0) {
        errorText.textContent = "Please enter a valid amount paid.";
        return;
    }

    var queue = getData("queue");
    var item = findById(queue, Number(queueSelect.value));
    var paid = Number(amountInput.value);

    if (paid < item.price) {
        errorText.textContent = "Amount paid is less than the total (" + peso(item.price) + ").";
        return;
    }

    var payments = getData("payments");
    payments.push({
        id: nextId(payments),
        date: today(),
        queueNo: item.queueNo,
        customerName: item.customerName,
        service: item.service,
        total: item.price,
        paid: paid,
        change: paid - item.price,
        method: methodInput.value
    });
    saveData("payments", payments);

    // Mark the queue record as paid
    item.paid = true;
    saveData("queue", queue);

    messageText.textContent = "Payment saved! Change: " + peso(paid - item.price);
    errorText.textContent = "";
    form.reset();
    fillQueueDropdown();
    showPayments();
});

// ---------- Show payment history (with search + filters) ----------
function showPayments() {
    var payments = getData("payments");
    var keyword = searchInput.value.toLowerCase();
    var methodFilter = filterMethod.value;
    var dateFilter = filterDate.value;
    var rows = "";
    var sales = 0;

    for (var i = 0; i < payments.length; i++) {
        var p = payments[i];

        var text = (p.queueNo + " " + p.customerName).toLowerCase();
        var matchText = text.indexOf(keyword) !== -1;
        var matchMethod = methodFilter === "" || p.method === methodFilter;
        var matchDate = dateFilter === "" || p.date === dateFilter;

        if (matchText && matchMethod && matchDate) {
            sales += p.total;
            rows += "<tr>" +
                "<td>" + p.id + "</td>" +
                "<td>" + p.date + "</td>" +
                "<td>" + p.queueNo + "</td>" +
                "<td>" + esc(p.customerName) + "</td>" +
                "<td>" + esc(p.service) + "</td>" +
                "<td>" + peso(p.total) + "</td>" +
                "<td>" + peso(p.paid) + "</td>" +
                "<td>" + peso(p.change) + "</td>" +
                "<td>" + p.method + "</td>" +
                "</tr>";
        }
    }

    if (rows === "") {
        rows = "<tr><td colspan='9'>No payments found.</td></tr>";
    }
    table.innerHTML = rows;
    document.getElementById("totalSales").textContent = peso(sales);
}

searchInput.addEventListener("input", showPayments);
filterMethod.addEventListener("change", showPayments);
filterDate.addEventListener("change", showPayments);

fillQueueDropdown();
showPayments();