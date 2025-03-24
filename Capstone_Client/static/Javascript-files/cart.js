document.addEventListener('DOMContentLoaded', function() {
  // Quantity control buttons
  document.querySelectorAll('.qty-btn').forEach(button => {
      button.addEventListener('click', function() {
          const input = this.parentElement.querySelector('.quantity');
          let currentValue = parseInt(input.value);
          
          if (this.classList.contains('minus')) {
              currentValue = Math.max(1, currentValue - 1);
          } else {
              currentValue += 1;
          }
          
          input.value = currentValue;
          updateCartItem(this.closest('.cart-item'));
      });
  });

  // Manual quantity input handling
  document.querySelectorAll('.quantity').forEach(input => {
      input.addEventListener('change', function() {
          if (this.value < 1) this.value = 1;
          updateCartItem(this.closest('.cart-item'));
      });
  });

  // Remove item handler
  document.querySelectorAll('.remove-btn').forEach(button => {
      button.addEventListener('click', function() {
          const cartItem = this.closest('.cart-item');
          const cartId = cartItem.dataset.id;
          
          if (confirm('Are you sure you want to remove this item?')) {
              fetch('remove_from_cart.php', {
                  method: 'POST',
                  headers: { 'Content-Type': 'application/json' },
                  body: JSON.stringify({ cart_id: cartId })
              })
              .then(response => response.json())
              .then(data => {
                  if (data.success) {
                      cartItem.remove();
                      updateCartTotal();
                  } else {
                      alert('Failed to remove item. Please try again.');
                  }
              });
          }
      });
  });

  // Checkout handler
  document.querySelector('.checkout-btn')?.addEventListener('click', () => {
      if (!document.querySelectorAll('.cart-item').length) {
          alert('Your cart is empty!');
          return;
      }

      fetch('cart-rec.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              localStorage.setItem('checkoutStatus', 'Waiting for seller approval');
              window.location.href = 'profile.php';
          } else {
              alert(data.message || 'Checkout failed. Please try again.');
          }
      });
  });

  // Cart update function
  function updateCartItem(cartItem) {
      const cartId = cartItem.dataset.id;
      const quantity = cartItem.querySelector('.quantity').value;
      
      fetch('update_cart.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ cart_id: cartId, quantity: quantity })
      })
      .then(response => response.json())
      .then(data => {
          if (data.success) {
              cartItem.querySelector('.item-total').textContent = 
                  '₱' + parseFloat(data.total_price).toFixed(2);
              updateCartTotal();
          }
      });
  }

  // Total calculation function
  function updateCartTotal() {
      const totals = Array.from(document.querySelectorAll('.item-total'))
          .map(el => parseFloat(el.textContent.replace(/[^0-9.-]+/g, "")))
          .reduce((sum, val) => sum + val, 0);
      
      const tax = totals * 0.08;
      const total = totals + tax;
      
      document.querySelector('.subtotal').textContent = '₱' + totals.toFixed(2);
      document.querySelector('#tax').textContent = '₱' + tax.toFixed(2);
      document.querySelector('#total').textContent = '₱' + total.toFixed(2);
  }
});