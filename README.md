# Product Inventory & Stock Counter (Mini System)

සාප්පුවක items සහ stock ප්‍රමාණය කළමනාකරණය කරන mini system එක.

## 📁 Files Included
- `schema.sql`: Database schema & sample items.
- `db.php`: MySQL PDO database connection.
- `actions.php`: PHP endpoints for adding items and updating stock count (+ / -) via AJAX.
- `index.php`: Main dashboard UI listing products and stock counts.
- `style.css`: Modern responsive styles, highlighting low stock in red.
- `script.js`: Instant live search bar filtering and AJAX buttons.

---

## 🚀 Setup Instructions (XAMPP / WAMP / Laragon)

### 1. Database Setup
1. **XAMPP / WAMP Control Panel** එක open කර **Apache** සහ **MySQL** Start කරන්න.
2. Browser එකේ `http://localhost/phpmyadmin` වෙත යන්න.
3. **SQL** tab එකට ගොස් `schema.sql` හි ඇති queries run කරන්න (හෝ `inventory_db` නමින් Database එකක් සාදා Import කරන්න).

### 2. Project Files Run කිරීම
1. මෙම files සියල්ල ඔබගේ XAMPP `htdocs` ෆෝල්ඩරයේ අලුත් folder එකක් සාදා (උදා: `htdocs/inventory-system/`) copy කරන්න.
2. Browser එකෙන් පහත link එක open කරන්න:
   ```
   http://localhost/inventory-system/
   ```

---

## ⚡ Features
- **Live Search**: කිසිදු reload එකකින් තොරව Product නම type කරන විට ක්ෂණිකව filter වීම.
- **Stock Counter (+ / -)**: Stock ප්‍රමාණය ක්ෂණිකව වැඩි / අඩු කිරීම (AJAX).
- **Low Stock Red Badge**: Quantity එක 5ට අඩු (හෝ 0) වූ විට රතු පාටින් `Low Stock` ලෙස alert වීම.
- **Add New Product**: Popup modal එකක් මගින් අලුත් items database එකට ඇතුළත් කිරීම.
