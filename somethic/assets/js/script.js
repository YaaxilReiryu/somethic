/**
 * SOMETHIC - Vanilla JavaScript Interactions
 * Pure JavaScript without external frameworks
 */

document.addEventListener('DOMContentLoaded', function () {
  // 1. Delete Confirmation Dialogs
  const deleteButtons = document.querySelectorAll('.btn-delete-confirm');
  deleteButtons.forEach(function (button) {
    button.addEventListener('click', function (e) {
      const itemName = this.getAttribute('data-name') || 'this item';
      if (!confirm(`Are you sure you want to delete ${itemName}? This action cannot be undone.`)) {
        e.preventDefault();
      }
    });
  });

  // 2. Reservation Product Stock Limiter
  const productSelect = document.getElementById('reservation_product_id');
  const quantityInput = document.getElementById('reservation_quantity');
  const stockHelp = document.getElementById('stock_feedback');

  if (productSelect && quantityInput) {
    function updateStockConstraint() {
      const selectedOption = productSelect.options[productSelect.selectedIndex];
      if (selectedOption && selectedOption.value) {
        const availableStock = parseInt(selectedOption.getAttribute('data-stock') || '0', 10);
        quantityInput.max = availableStock;
        if (availableStock <= 0) {
          quantityInput.value = 0;
          quantityInput.disabled = true;
          if (stockHelp) {
            stockHelp.textContent = 'Selected handbag is currently Out of Stock.';
            stockHelp.className = 'text-danger small mt-1';
          }
        } else {
          quantityInput.disabled = false;
          if (parseInt(quantityInput.value, 10) > availableStock) {
            quantityInput.value = availableStock;
          }
          if (parseInt(quantityInput.value, 10) < 1) {
            quantityInput.value = 1;
          }
          if (stockHelp) {
            stockHelp.textContent = `Available stock: ${availableStock} unit(s).`;
            stockHelp.className = 'text-muted small mt-1';
          }
        }
      }
    }

    productSelect.addEventListener('change', updateStockConstraint);
    updateStockConstraint();
  }

  // 3. Minimum Pickup Date Validation (cannot reserve in the past)
  const dateInput = document.getElementById('reservation_pickup_date');
  if (dateInput) {
    const today = new Date().toISOString().split('T')[0];
    dateInput.min = today;
  }
});
