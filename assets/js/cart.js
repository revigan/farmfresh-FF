function updateQuantity(cartItemId, action, value = null) {
  let url = "actions/update_cart.php";
  let data = {
    cart_item_id: cartItemId,
    action: action,
  };

  if (action === "set" && value !== null) {
    data.quantity = parseInt(value);
    // Validasi input
    if (isNaN(data.quantity) || data.quantity < 1) {
      alert("Jumlah tidak valid");
      return;
    }
  }

  // Tampilkan loading
  const input = document.getElementById(`quantity_${cartItemId}`);
  const originalValue = input.value;
  input.disabled = true;

  fetch(url, {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify(data),
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        location.reload();
      } else {
        alert(data.message || "Gagal mengupdate keranjang");
        input.value = originalValue;
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan");
      input.value = originalValue;
    })
    .finally(() => {
      input.disabled = false;
    });
}

function removeItem(cartItemId) {
  if (confirm("Apakah Anda yakin ingin menghapus item ini dari keranjang?")) {
    fetch("actions/remove_from_cart.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        cart_item_id: cartItemId,
      }),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          location.reload();
        } else {
          alert(data.message || "Gagal menghapus item dari keranjang");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Terjadi kesalahan sistem");
      });
  }
}
