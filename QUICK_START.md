# Quick Reference - Flex & Bliss XAMPP Deployment

## 🚀 Quick Start (5 Steps)

1. **Start XAMPP** → Open XAMPP Control Panel → Start Apache & MySQL
2. **Create Database** → Open http://localhost/phpmyadmin → Import `database.sql`
3. **Copy Files** → Copy project folder to `C:\xampp\htdocs`
4. **Access Website** → http://localhost/Ekra Main website - Copy/index.html
5. **Access Admin** → http://localhost/Ekra Main website - Copy/admin/index.php

---

## 📁 Important Files Created

### Backend (PHP)
- `includes/config.php` - Database configuration
- `includes/db.php` - Database connection
- `includes/logger.php` - Logging utility
- `database.sql` - Database schema

### API Endpoints
- `api/register_user.php` - User registration
- `api/submit_order.php` - Order processing
- `api/get_orders.php` - Get orders for admin

### Admin Panel
- `admin/index.php` - Dashboard
- `admin/view_logs.php` - Log viewer
- `admin/styles.css` - Admin styles

### Logs (Auto-created)
- `logs/registrations.txt` - User registrations log
- `logs/orders.txt` - Orders log

---

## 🔗 URLs

| Page | URL |
|------|-----|
| **Website** | http://localhost/Ekra Main website - Copy/index.html |
| **Admin Dashboard** | http://localhost/Ekra Main website - Copy/admin/index.php |
| **phpMyAdmin** | http://localhost/phpmyadmin |

---

## 🗄️ Database Info

- **Name:** flexbliss_db
- **User:** root
- **Password:** (empty)
- **Tables:** users, orders, order_items

---

## ✅ Test Checklist

### Test User Registration
1. Go to website
2. Click "Create Your Account"
3. Fill: Name, Email, Phone, Address
4. Click "Save Changes"
5. ✓ Check admin dashboard for new user
6. ✓ Check `logs/registrations.txt`

### Test Order
1. Add products to cart
2. Click cart icon → "Proceed to Checkout"
3. Fill customer details
4. Click "Confirm Order"
5. ✓ Check admin dashboard for new order
6. ✓ Check `logs/orders.txt`

---

## 📊 Admin Features

### Dashboard Shows:
- Total Users
- Total Orders  
- Pending Orders
- Total Revenue

### Can View:
- Recent Registrations (with details)
- Recent Orders (with items)
- Complete Log Files
- Download Logs

---

## 🔒 Security

✅ SQL injection protection (PDO prepared statements)
✅ Input validation and sanitization
✅ Protected logs directory (.htaccess)
✅ Proper error handling

---

## 🛠️ Troubleshooting

**Problem:** Can't connect to database
**Fix:** Make sure MySQL is running in XAMPP, database is imported

**Problem:** Orders not saving
**Fix:** Check browser console (F12) for errors, verify API paths

**Problem:** Log files not creating
**Fix:** Check write permissions on `logs` folder

---

## 📝 What Gets Logged

### Registrations Log
```
========================================
REGISTRATION #1
========================================
Timestamp: 2024-11-27 18:45:30
Name: John Doe
Email: john@example.com
Phone: 01234567890
Address: Dhaka, Bangladesh
========================================
```

### Orders Log
```
========================================
ORDER #ORD-20241127-00001
========================================
Timestamp: 2024-11-27 19:10:45
Customer Name: Jane Smith
Customer Email: jane@example.com
Customer Phone: 01987654321
Shipping Address: Gulshan, Dhaka
Payment Method: bKash
---
ORDER ITEMS:
- Product Name (Qty: 2) - ৳1,500.00 each = ৳3,000.00
---
TOTAL AMOUNT: ৳3,000.00
Status: Pending
========================================
```

---

## 💡 Tips

- **Backup Data:** Export database regularly via phpMyAdmin
- **View Logs:** Admin panel → View Logs → Download
- **Log Location:** Direct path: `logs/registrations.txt` and `logs/orders.txt`
- **Test First:** Try registration and order before real use
- **Development Only:** This setup is for localhost testing only

---

For complete documentation, see [README_DEPLOYMENT.md](file:///c:/Users/HP/Desktop/Sipon/Ekra%20Main%20website%20-%20Copy/README_DEPLOYMENT.md)
