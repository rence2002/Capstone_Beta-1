console.log("cart.js loaded");

let cartData = [];

function removeItemFromCart(itemIndex) {
  cartData.splice(itemIndex, 1);
  localStorage.setItem('cartData', JSON.stringify(cartData));
  renderCartItems();
  updateCartTotal();
}

function updateCartTotal() {
  const checkedItems = document.querySelectorAll('.item-checkbox:checked');
  const subtotal = Array.from(checkedItems).reduce((acc, checkbox) => {
    const index = parseInt(checkbox.getAttribute('data-index'));
    const item = cartData[index];
    return acc + item.price * item.quantity;
  }, 0);

  const tax = subtotal * 0.08;
  const total = subtotal + tax;

  document.getElementById('subtotal').textContent = `₱ ${subtotal.toFixed(2)}`;
  document.getElementById('tax').textContent = `₱ ${tax.toFixed(2)}`;
  document.getElementById('total').textContent = `₱ ${total.toFixed(2)}`;
}

function updateQuantity(index, change) {
  cartData[index].quantity += change;
  if (cartData[index].quantity < 1) {
    removeItemFromCart(index);
  } else {
    localStorage.setItem('cartData', JSON.stringify(cartData));
    renderCartItems();
  }
}

function removeItemFromCart(itemIndex) {
  cartData.splice(itemIndex, 1);
  localStorage.setItem('cartData', JSON.stringify(cartData));
  renderCartItems();
}

function renderCartItems() {
  const cartItems = document.getElementById('cart-items');
  if (!cartItems) {
    console.error("Cart items element not found");
    return;
  }

  cartItems.innerHTML = '';
  if (cartData.length === 0) {
    cartItems.innerHTML = '<p>Your cart is empty</p>';
  } else {
    cartData.forEach((item, index) => {
      const cartItemSection = document.createElement('div');
      cartItemSection.classList.add('cart-item-section');
      
      const cartItem = document.createElement('div');
      cartItem.classList.add('cart-item');
      cartItem.innerHTML = `
        <div class="item-left">
          <label class="checkbox-container">
            <input type="checkbox" class="item-checkbox" data-index="${index}" ${item.checked ? 'checked' : ''}>
            <span class="checkmark"></span>
          </label>
          <img src="${item.image}" alt="${item.name}">
          <h3>${item.name}</h3>
        </div>
        <div class="item-right">
          <div class="quantity-control">
            <button class="quantity-btn minus" onclick="updateQuantity(${index}, -1)">-</button>
            <span class="quantity">${item.quantity}</span>
            <button class="quantity-btn plus" onclick="updateQuantity(${index}, 1)">+</button>
          </div>
          <p>Price: ₱<span class="price">${(item.price * item.quantity).toFixed(2)}</span></p>
          <button class="remove-item" onclick="removeItemFromCart(${index})">Remove</button>
        </div>
      `;
      cartItemSection.appendChild(cartItem);
      cartItems.appendChild(cartItemSection);
    });
  }
  updateCartTotal();
}

document.addEventListener('DOMContentLoaded', function() {
  const cartContainer = document.getElementById('cart-container');

  if (!cartContainer) {
    console.error("Cart container element not found");
    return;
  }

  cartData = JSON.parse(localStorage.getItem('cartData')) || [];
  console.log("Loaded cart data from localStorage:", cartData);

  renderCartItems();
});

document.addEventListener('DOMContentLoaded', function() {
  const cartContainer = document.getElementById('cart-container');

  if (!cartContainer) {
    console.error("Cart container element not found");
    return;
  }

  cartData = JSON.parse(localStorage.getItem('cartData')) || [];
  console.log("Loaded cart data from localStorage:", cartData);

  renderCartItems();

   // Add event listener for checkbox changes
   document.getElementById('cart-items').addEventListener('change', function(event) {
    if (event.target.classList.contains('item-checkbox')) {
      const index = parseInt(event.target.getAttribute('data-index'));
      cartData[index].checked = event.target.checked;
      localStorage.setItem('cartData', JSON.stringify(cartData));
      updateCartTotal();
    }
  });
});

//for profile

document.getElementById('checkout').addEventListener('click', function() {
  // Set the checkout status
  localStorage.setItem('checkoutStatus', 'waiting for seller\'s approval');

  // Optionally, you can redirect to the profile page after checkout
  window.location.href = 'Profile.html'; // Change this to your profile page's URL
});