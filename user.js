// load from localStorage
let users = JSON.parse(localStorage.getItem("users")) || [];

// SHOW REGISTER
function openRegister() {
    document.getElementById("loginBox").style.display = "none";
    document.getElementById("registerBox").style.display = "block";
}

// SHOW LOGIN
function openLogin() {
    document.getElementById("registerBox").style.display = "none";
    document.getElementById("loginBox").style.display = "block";
}

// REGISTER USER
function register() {
    const username = document.getElementById("regUser").value.trim();
    const password = document.getElementById("regPass").value.trim();
    const role = document.getElementById("regRole").value;

    if (username === "" || password === "") {
        alert("Semua field harus diisi!");
        return;
    }

    const exists = users.some(u => u.username === username);
    if (exists) {
        alert("Username sudah digunakan!");
        return;
    }

    const newUser = { username, password, role };
    users.push(newUser);
    localStorage.setItem("users", JSON.stringify(users));

    alert("Akun berhasil dibuat! Silakan login.");
    openLogin();
}

// LOGIN USER
function login() {
    const user = document.getElementById("loginUser").value.trim();
    const pass = document.getElementById("loginPass").value.trim();

    const match = users.find(u => u.username === user && u.password === pass);

    if (!match) {
        alert("Username atau password salah!");
        return;
    }

    localStorage.setItem("currentUser", JSON.stringify(match));

    // Arahkan user ke halaman home kamu
    window.location.href = "home.html";
}

// LOGOUT (gunakan di home.html)
function logout() {
    localStorage.removeItem("currentUser");
    window.location.href = "index.html";
}
