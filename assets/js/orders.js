let currentOrderItemId = null;
let selectedRating = 0;

// Fungsi untuk menampilkan modal rating
function showRatingModal(orderItemId, productName) {
  // Set nilai untuk digunakan saat submit
  currentOrderItemId = orderItemId;

  // Set nama produk di modal
  document.getElementById("productName").textContent = productName;

  // Reset form
  resetStars();
  document.getElementById("review").value = "";

  // Tampilkan modal
  new bootstrap.Modal(document.getElementById("ratingModal")).show();
}

// Event listener untuk bintang rating
document.querySelectorAll(".star-rating").forEach((star) => {
  star.onclick = function () {
    selectedRating = parseInt(this.dataset.rating);
    document.querySelectorAll(".star-rating").forEach((s) => {
      if (parseInt(s.dataset.rating) <= selectedRating) {
        s.classList.remove("bx-star");
        s.classList.add("bxs-star");
      } else {
        s.classList.remove("bxs-star");
        s.classList.add("bx-star");
      }
    });
  };

  // Tambahkan hover effect
  star.onmouseover = function () {
    const rating = parseInt(this.dataset.rating);
    document.querySelectorAll(".star-rating").forEach((s) => {
      if (parseInt(s.dataset.rating) <= rating) {
        s.classList.remove("bx-star");
        s.classList.add("bxs-star");
      }
    });
  };

  star.onmouseout = function () {
    document.querySelectorAll(".star-rating").forEach((s) => {
      if (parseInt(s.dataset.rating) <= selectedRating) {
        s.classList.remove("bx-star");
        s.classList.add("bxs-star");
      } else {
        s.classList.remove("bxs-star");
        s.classList.add("bx-star");
      }
    });
  };
});

// Reset stars saat modal dibuka
function resetStars() {
  selectedRating = 0;
  document.querySelectorAll(".star-rating").forEach((star) => {
    star.classList.remove("bxs-star");
    star.classList.add("bx-star");
  });
}

// Fungsi untuk submit rating
function submitRating() {
  if (!selectedRating) {
    alert("Silakan pilih rating terlebih dahulu");
    return;
  }

  fetch("actions/submit_rating.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: `order_item_id=${currentOrderItemId}&rating=${selectedRating}&review=${
      document.getElementById("review").value
    }`,
  })
    .then((response) => response.json())
    .then((data) => {
      if (data.success) {
        alert("Terima kasih atas penilaian Anda");
        document
          .getElementById("ratingModal")
          .querySelector(".btn-close")
          .click();
        location.reload();
      } else {
        alert(data.message || "Gagal menyimpan rating");
      }
    })
    .catch((error) => {
      console.error("Error:", error);
      alert("Terjadi kesalahan sistem");
    });
}

function confirmReceived(orderId) {
  if (!orderId) {
    alert("ID Pesanan tidak valid");
    return;
  }

  if (confirm("Apakah Anda yakin telah menerima pesanan ini?")) {
    // Pastikan orderId adalah integer
    const data = {
      order_id: parseInt(orderId),
    };

    fetch("actions/confirm_received.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(data),
    })
      .then((response) => response.json())
      .then((data) => {
        if (data.success) {
          alert("Pesanan berhasil dikonfirmasi");
          location.reload();
        } else {
          alert(data.message || "Gagal mengkonfirmasi pesanan");
        }
      })
      .catch((error) => {
        console.error("Error:", error);
        alert("Terjadi kesalahan sistem");
      });
  }
}
