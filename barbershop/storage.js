var SERVICES = [
    { name: "Haircut", price: 150 },
    { name: "Shave", price: 100 },
    { name: "Haircut + Shave", price: 220 },
    { name: "Hair Color", price: 800 },
    { name: "Hair Spa", price: 600 },
    { name: "Rebond", price: 1500 }
];

function getData(key) {
    var data = localStorage.getItem(key);
    if (data) {
        return JSON.parse(data);
    }
    return [];
}

function saveData(key, list) {
    localStorage.setItem(key, JSON.stringify(list));
}

function nextId(list) {
    var max = 0;
    for (var i = 0; i < list.length; i++) {
        if (list[i].id > max) {
            max = list[i].id;
        }
    }
    return max + 1;
}

function findById(list, id) {
    for (var i = 0; i < list.length; i++) {
        if (list[i].id === id) {
            return list[i];
        }
    }
    return null;
}

function getServicePrice(serviceName) {
    for (var i = 0; i < SERVICES.length; i++) {
        if (SERVICES[i].name === serviceName) {
            return SERVICES[i].price;
        }
    }
    return 0;
}

function requireLogin() {
    if (localStorage.getItem("loggedIn") !== "yes") {
        window.location.href = "index.html";
    }
}

function logout() {
    localStorage.removeItem("loggedIn");
    window.location.href = "index.html";
}

function today() {
    var d = new Date();
    var month = String(d.getMonth() + 1).padStart(2, "0");
    var day = String(d.getDate()).padStart(2, "0");
    return d.getFullYear() + "-" + month + "-" + day;
}

function nowTime() {
    return new Date().toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

function peso(amount) {
    return "₱" + Number(amount).toFixed(2);
}

// Prevents HTML from being injected through input fields
function esc(text) {
    var div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}