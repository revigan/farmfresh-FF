function addToCart(productId) {
  const formData = new FormData();
  formData.append("product_id", productId);
  formData.append("quantity", 1);

  fetch("actions/add_to_cart.php", {
    method: "POST",
    body: formData,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        // Update cart badge
        const cartCount = document.querySelector(".cart-count");
        if (cartCount) {
          cartCount.textContent = data.cart_count;
        }

        // Show success message with SweetAlert2
        Swal.fire({
          title: "Berhasil!",
          text: "Produk berhasil ditambahkan ke keranjang",
          icon: "success",
          showCancelButton: true,
          confirmButtonText: "Lihat Keranjang",
          cancelButtonText: "Lanjut Belanja",
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "cart.php";
          }
        });
      } else {
        Swal.fire({
          title: "Gagal!",
          text: data.message,
          icon: "error",
        });
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      Swal.fire({
        title: "Error!",
        text: "Terjadi kesalahan sistem",
        icon: "error",
      });
    });
}
