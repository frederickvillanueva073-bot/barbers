requireLogin();

document.getElementById("todayDate").textContent = today();

var customers = getData("customers");
var barbers = getData("barbers");
var queue = getData("queue");
var payments = getData("payments");

document.getElementById("countCustomers").textContent = customers.length;
document.getElementById("countBarbers").textContent = barbers.length;

var waiting = 0;
for (var i = 0; i < queue.length; i++) {
    if (queue[i].date === today() && queue[i].status === "Waiting") {
        waiting++;
    }
}
document.getElementById("countWaiting").textContent = waiting;

var sales = 0;
for (var j = 0; j < payments.length; j++) {
    if (payments[j].date === today()) {
        sales += payments[j].total;
    }
}
document.getElementById("countSales").textContent = peso(sales);

// Show the service list
var serviceList = document.getElementById("serviceList");
for (var k = 0; k < SERVICES.length; k++) {
    serviceList.innerHTML += "<tr><td>" + SERVICES[k].name + "</td><td>" + peso(SERVICES[k].price) + "</td></tr>";
}