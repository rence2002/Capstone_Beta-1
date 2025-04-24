function showSection(id) {
  // Hide all sections
  document.querySelectorAll('.section-container').forEach(section => {
    section.classList.add('hidden');
  });

  // Remove 'active' from all tab buttons
  document.querySelectorAll('.tab-btn').forEach(btn => {
    btn.classList.remove('active');
  });

  // Show the selected section
  document.getElementById(id).classList.remove('hidden');

  // Activate the clicked tab
  const tabs = ['pending-orders', 'order-status', 'product-status', 'purchase-history'];
  const index = tabs.indexOf(id);
  document.querySelectorAll('.tab-btn')[index].classList.add('active');
}
