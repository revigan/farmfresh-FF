document.addEventListener("DOMContentLoaded", function () {
  // Initialize tooltips
  var tooltipTriggerList = [].slice.call(
    document.querySelectorAll('[data-bs-toggle="tooltip"]')
  );
  var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    return new bootstrap.Tooltip(tooltipTriggerEl);
  });

  // Animate back button on hover
  const backBtn = document.querySelector(".btn-light");
  backBtn.addEventListener("mouseenter", function () {
    this.style.transform = "translateX(-5px)";
  });
  backBtn.addEventListener("mouseleave", function () {
    this.style.transform = "translateX(0)";
  });
});
