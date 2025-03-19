document.getElementById("profile-btn").addEventListener("click", function(event) {
    event.preventDefault(); // Prevents page from refreshing
    let dropdown = document.getElementById("profile-dropdown");
    dropdown.style.display = dropdown.style.display === "block" ? "none" : "block";
});

// Close dropdown when clicking outside
document.addEventListener("click", function(event) {
    let profileContainer = document.querySelector(".profile-container");
    let dropdown = document.getElementById("profile-dropdown");

    if (!profileContainer.contains(event.target)) {
        dropdown.style.display = "none";
    }
});
