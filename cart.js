let cart = JSON.parse(localStorage.getItem("cart")) || [];

function saveCart() {
    localStorage.setItem("cart", JSON.stringify(cart));
}

function addToCart(productId) {
    fetch(`https://dummyjson.com/products/${productId}`)
        .then(res => res.json())
        .then(p => {
            const found = cart.find(i => i.id === p.id);

            if (found) found.qty++;
            else cart.push({
                id: p.id,
                title: p.title,
                price: p.price,
                image: p.thumbnail,
                qty: 1
            });

            saveCart();
            alert("Ditambahkan ke keranjang!");
        });
}
