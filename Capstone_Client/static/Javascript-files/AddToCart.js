document.addEventListener('DOMContentLoaded', function() {
    console.log("addToCart.js loaded");
  
    document.querySelectorAll('.add-to-cart').forEach(button => {
        button.addEventListener('click', function(event) {
            event.preventDefault();
            console.log("Add to Cart button clicked");
            const item = {
                name: this.getAttribute('data-name'),
                image: this.getAttribute('data-image'),
                price: parseFloat(this.getAttribute('data-price')),
                quantity: 1
            };
            console.log("Item data:", item);
            addItemToCart(item, this); // Pass the button element to the function
        });
    });
});

function addItemToCart(item, button) {
    console.log("Adding item to cart:", item);
    let cartData = JSON.parse(localStorage.getItem('cartData')) || [];
    console.log("Current cart data:", cartData);
  
    const existingItem = cartData.find(cartItem => cartItem.name === item.name);
    if (existingItem) {
        existingItem.quantity += 1;
        console.log("Increased quantity for existing item");
    } else {
        cartData.push(item);
        console.log("Added new item to cart");
    }
  
    localStorage.setItem('cartData', JSON.stringify(cartData));
    console.log("Updated cart data in localStorage");
  
    // Show notification
    showNotification(button);
}

function showNotification(button) {
    // Find the closest notification element related to the clicked button
    const notification = button.parentElement.querySelector('.notification');
    if (!notification) {
        console.error("Notification element not found!");
        return; // Exit the function if the element is not found
    }
    console.log("Showing notification...");
    notification.classList.remove('hidden');
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.classList.add('hidden');
    }, 3000);
}