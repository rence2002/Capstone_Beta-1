document.addEventListener("DOMContentLoaded", function () {
    const modal = document.getElementById("paymentStatusModal");
    const closeModal = document.querySelector(".close");
    const openModalButtons = document.querySelectorAll(".openModal");
    const orderIdInput = document.getElementById("orderIdInput");

    // Open modal when "Accept" button is clicked
    openModalButtons.forEach(button => {
        button.addEventListener("click", function () {
            const orderId = this.getAttribute("data-order-id");
            orderIdInput.value = orderId; // Set the order ID in the hidden input
            modal.style.display = "block";
        });
    });

    // Close modal when "X" is clicked
    closeModal.addEventListener("click", function () {
        modal.style.display = "none";
    });

    // Close modal when clicking outside the modal content
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none";
        }
    });
});