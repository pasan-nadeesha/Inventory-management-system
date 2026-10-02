// script.js - Live Search & Stock Counter AJAX Handling

document.addEventListener('DOMContentLoaded', () => {
    const searchInput = document.getElementById('searchInput');
    const tableBody = document.getElementById('inventoryTableBody');
    const emptySearchRow = document.getElementById('emptySearchRow');
    const totalProductsCount = document.getElementById('totalProductsCount');
    const lowStockCount = document.getElementById('lowStockCount');

    // 1. Live Search Bar Filter
    searchInput.addEventListener('input', function () {
        const query = this.value.trim().toLowerCase();
        const rows = tableBody.querySelectorAll('tr.product-row');
        let visibleCount = 0;

        rows.forEach(row => {
            const productName = row.getAttribute('data-name') || '';
            if (productName.includes(query)) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Show "No products found" message if nothing matches
        if (visibleCount === 0 && rows.length > 0) {
            emptySearchRow.style.display = '';
        } else {
            emptySearchRow.style.display = 'none';
        }
    });

    // 2. Stock Increment & Decrement (+ / -) via AJAX
    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-counter');
        if (!btn) return;

        const row = btn.closest('tr');
        const id = row.getAttribute('data-id');
        const change = parseInt(btn.getAttribute('data-change'), 10);
        const qtySpan = row.querySelector('.qty-val');
        const badgeSpan = row.querySelector('.badge');

        const currentQty = parseInt(qtySpan.textContent, 10);
        if (change === -1 && currentQty <= 0) {
            alert('Stock quantity cannot be less than 0.');
            return;
        }

        btn.disabled = true;

        const formData = new FormData();
        formData.append('action', 'update_stock');
        formData.append('id', id);
        formData.append('change', change);

        fetch('actions.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            if (data.status === 'success') {
                // Update quantity display
                qtySpan.textContent = data.quantity;

                // Update badge class and text
                badgeSpan.className = `badge ${data.status_info.class}`;
                badgeSpan.textContent = data.status_info.label;

                // Update summary metrics
                recalculateStats();
            } else {
                alert(data.message || 'Error updating stock');
            }
        })
        .catch(err => {
            btn.disabled = false;
            console.error('Fetch error:', err);
            alert('Failed to connect to the server.');
        });
    });

    // 3. Delete Product via AJAX
    tableBody.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete');
        if (!btn) return;

        const row = btn.closest('tr');
        const id = row.getAttribute('data-id');
        const name = row.getAttribute('data-name');

        if (!confirm(`Are you sure you want to delete "${name}"?`)) {
            return;
        }

        const formData = new FormData();
        formData.append('action', 'delete');
        formData.append('id', id);

        fetch('actions.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                row.remove();
                recalculateStats();
            } else {
                alert(data.message || 'Error deleting product');
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('Failed to connect to the server.');
        });
    });

    // 4. Add Product Form Submission
    const addProductForm = document.getElementById('addProductForm');
    const addProductModal = document.getElementById('addProductModal');

    addProductForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'add');

        fetch('actions.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                const p = data.product;
                const newRow = document.createElement('tr');
                newRow.className = 'product-row';
                newRow.setAttribute('data-id', p.id);
                newRow.setAttribute('data-name', p.product_name.toLowerCase());

                newRow.innerHTML = `
                    <td>#${p.id}</td>
                    <td class="product-name">${p.product_name}</td>
                    <td class="price-text">Rs. ${p.price}</td>
                    <td>
                        <div class="counter-box">
                            <button type="button" class="btn-counter" data-change="-1" title="Decrease Stock">-</button>
                            <span class="qty-val">${p.quantity}</span>
                            <button type="button" class="btn-counter" data-change="1" title="Increase Stock">+</button>
                        </div>
                    </td>
                    <td>
                        <span class="badge ${p.status.class}">
                            ${p.status.label}
                        </span>
                    </td>
                    <td style="text-align: right;">
                        <button type="button" class="btn-delete" title="Delete product">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="3 6 5 6 21 6"></polyline>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path>
                            </svg>
                        </button>
                    </td>
                `;

                // Remove placeholder if present
                const initialEmpty = document.getElementById('initialEmpty');
                if (initialEmpty) initialEmpty.remove();

                tableBody.prepend(newRow);
                addProductForm.reset();
                closeModal();
                recalculateStats();
            } else {
                alert(data.message || 'Error adding product.');
            }
        })
        .catch(err => {
            console.error('Fetch error:', err);
            alert('Failed to connect to the server.');
        });
    });

    // Recalculate stats dynamically
    function recalculateStats() {
        const rows = tableBody.querySelectorAll('tr.product-row');
        totalProductsCount.textContent = rows.length;

        let lowCount = 0;
        rows.forEach(r => {
            const qty = parseInt(r.querySelector('.qty-val').textContent, 10);
            if (qty <= 5) {
                lowCount++;
            }
        });
        lowStockCount.textContent = lowCount;
    }
});

// Modal helpers
function openModal() {
    document.getElementById('addProductModal').classList.add('active');
    document.getElementById('p_name').focus();
}

function closeModal() {
    document.getElementById('addProductModal').classList.remove('active');
}
