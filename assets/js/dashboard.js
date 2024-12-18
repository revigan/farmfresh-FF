function addToCart(productId) {
  fetch("actions/add_to_cart.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      product_id: productId,
    }),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update cart count in navbar
        location.reload();
      } else {
        alert(data.message || "Gagal menambahkan ke keranjang");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan");
    });
}
