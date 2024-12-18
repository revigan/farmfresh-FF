// Preview image before upload
function previewImage(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();

    reader.onload = function (e) {
      const preview = document.getElementById("profileImagePreview");
      if (preview.tagName === "IMG") {
        preview.src = e.target.result;
      } else {
        // Replace div with img
        const img = document.createElement("img");
        img.src = e.target.result;
        img.id = "profileImagePreview";
        img.className = "rounded-circle border";
        img.style.width = "100px";
        img.style.height = "100px";
        img.style.objectFit = "cover";
        preview.parentNode.replaceChild(img, preview);
      }
    };

    reader.readAsDataURL(input.files[0]);
  }
}

// Form validation
document.getElementById("editProfileForm").onsubmit = function (e) {
  const phoneInput = this.querySelector('input[name="phone"]');
  if (phoneInput.value && !phoneInput.value.match(/^[0-9]{10,13}$/)) {
    e.preventDefault();
    alert("Nomor telepon tidak valid. Masukkan 10-13 digit angka.");
    return false;
  }
  return true;
};

document.getElementById("changePasswordForm").onsubmit = function (e) {
  const newPass = document.getElementById("newPassword").value;
  const confirmPass = document.getElementById("confirmPassword").value;

  if (newPass !== confirmPass) {
    e.preventDefault();
    alert("Konfirmasi password tidak sesuai");
    return false;
  }
  return true;
};