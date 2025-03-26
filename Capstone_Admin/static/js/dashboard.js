
function toggleDropdown() {
    document.getElementById("profileDropdown").classList.toggle("show");
}

// Close dropdown when clicking outside
window.addEventListener("click", function (event) {
    if (!event.target.closest(".profile-details")) {
        document.getElementById("profileDropdown").classList.remove("show");
    }
});
