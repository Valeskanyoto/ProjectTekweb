let users = JSON.parse(localStorage.getItem("users")) || [];

function loadUsers() {
    const table = document.getElementById("userTable");
    table.innerHTML = "";

    users.forEach((u, i) => {
        table.innerHTML += `
            <tr>
                <td>${u.username}</td>
                <td>${u.password}</td>
                <td>${u.role}</td>
                <td><button onclick="delUser(${i})">Delete</button></td>
            </tr>
        `;
    });
}

function delUser(index) {
    users.splice(index, 1);
    localStorage.setItem("users", JSON.stringify(users));
    loadUsers();
}

loadUsers();
