# Flex & Bliss E-commerce Website - XAMPP Deployment Guide

## Complete Step-by-Step Deployment Instructions

### Prerequisites

- XAMPP installed on Windows
- Web browser (Chrome, Firefox, Edge, etc.)
- Text editor (optional, for configuration changes)

---

## Step 1: Install and Start XAMPP

1. **Download XAMPP** from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. **Install XAMPP** to the default location (usually `C:\xampp`)
3. **Open XAMPP Control Panel** from Start Menu
4. **Start Apache** - Click "Start" button next to Apache
5. **Start MySQL** - Click "Start" button next to MySQL

Both should show green "Running" status.

---

## Step 2: Create Database

1. **Open phpMyAdmin**
   - In your browser, go to: `http://localhost/phpmyadmin`
   - Or click "Admin" button next to MySQL in XAMPP Control Panel

2. **Import Database**
   - Click "New" in the left sidebar or go to "Databases" tab
   - Click on "Import" tab at the top
   - Click "Choose File" button
   - Navigate to your project folder and select `database.sql`
   - Click "Go" button at the bottom
   - You should see a success message

3. **Verify Database Created**
   - Look for `flexbliss_db` in the left sidebar
   - Click on it to expand and verify tables: `users`, `orders`, `order_items`

---

## Step 3: Copy Website Files

1. **Locate htdocs folder**
   - Navigate to: `C:\xampp\htdocs`

2. **Copy your project**
   - Copy the entire "Ekra Main website - Copy" folder to `htdocs`
   - OR rename it to something simpler like "flexbliss" for easier URL access

3. **Folder structure should be:**
   ```
   C:\xampp\htdocs\Ekra Main website - Copy\
   ├── index.html
   ├── script.js
   ├── products.js
   ├── styles.css
   ├── database.sql
   ├── includes\
   ├── api\
   ├── admin\
   ├── logs\
   └── images\
   ```

---

## Step 4: Verify Configuration

The database configuration is already set for XAMPP defaults. If you need to change it:

1. Open `includes/config.php`
2. Verify these settings match your XAMPP installation:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_NAME', 'flexbliss_db');
   define('DB_USER', 'root');
   define('DB_PASS', ''); // Empty for default XAMPP
   ```

---

## Step 5: Set Folder Permissions

The `logs` folder needs write permissions (usually automatic on Windows):

1. Right-click on the `logs` folder
2. Select Properties
3. Go to Security tab
4. Ensure "Users" group has "Write" permission
5. Click OK

---

## Step 6: Access Your Website

### Main Website
Open your browser and go to:
- `http://localhost/Ekra Main website - Copy/index.html`
- OR if you renamed the folder: `http://localhost/flexbliss/index.html`

### Admin Dashboard
Access the admin panel at:
- `http://localhost/Ekra Main website - Copy/admin/index.php`
- OR: `http://localhost/flexbliss/admin/index.php`

---

## Step 7: Test the Functionality

### Test User Registration

1. On the main website, click "Create Your Account" in the top menu
2. Fill in:
   - Full Name: Test User
   - Email: test@example.com
   - Phone: 01234567890
   - Address: Dhaka, Bangladesh
3. Click "Save Changes"
4. You should see a success message

### Test Order Placement

1. Browse products on the homepage
2. Click "Add to Cart" on any product
3. Click the cart icon in the header
4. Click "Proceed to Checkout"
5. Fill in your information (it may auto-fill if you registered)
6. Click "Confirm Order"
7. You should receive an order confirmation with an Order ID

### Verify Admin Dashboard

1. Go to the admin panel URL (see Step 6)
2. You should see:
   - Total Users: 1
   - Total Orders: 1
   - Your registration in "Recent Registrations"
   - Your order in "Recent Orders"

### View Log Files

1. In the admin dashboard, click "View Logs" in the navigation
2. Switch between "Registrations Log" and "Orders Log"
3. You should see your test data formatted in text
4. You can download the log files using the "Download Log File" button

---

## Accessing Log Files Directly

Log files are stored in the `logs` folder:

- **Registrations:** `logs/registrations.txt`
- **Orders:** `logs/orders.txt`

You can open these files with any text editor (Notepad, Notepad++, etc.)

The logs folder is protected from web browser access for security.

---

## Troubleshooting

### Problem: Database Connection Error

**Solution:**
1. Make sure MySQL is running in XAMPP Control Panel
2. Verify database was created successfully in phpMyAdmin
3. Check `includes/config.php` settings match your XAMPP configuration

### Problem: Can't Access Website

**Solution:**
1. Make sure Apache is running in XAMPP Control Panel
2. Check the URL matches your folder name
3. Try accessing `http://localhost` first to ensure XAMPP is working

### Problem: Orders/Registrations Not Saving

**Solution:**
1. Check browser console for JavaScript errors (F12 → Console tab)
2. Verify database was imported correctly
3. Check that `logs` folder has write permissions
4. Look at XAMPP Apache error logs

### Problem: Log Files Not Creating

**Solution:**
1. Check folder permissions on `logs` directory
2. Verify Apache has write access
3. Check if PHP is running (open `http://localhost` and verify XAMPP welcome page)

---

## Important Notes

1. **This is for LOCAL DEVELOPMENT ONLY**
   - Do not use in production without proper security measures
   - Change database passwords
   - Configure proper user authentication
   - Enable HTTPS

2. **Backup Your Data**
   - Regularly backup the database
   - Keep copies of log files
   - Export database via phpMyAdmin: Export → Go

3. **Database Export** (For Backup)
   - In phpMyAdmin, select `flexbliss_db`
   - Click "Export" tab
   - Click "Go" button
   - Save the .sql file

---

## Default Access Information

- **Website URL:** `http://localhost/Ekra Main website - Copy/index.html`
- **Admin Dashboard:** `http://localhost/Ekra Main website - Copy/admin/index.php`
- **phpMyAdmin:** `http://localhost/phpmyadmin`
- **Database Name:** `flexbliss_db`
- **Database User:** `root`
- **Database Password:** (empty)

---

## Support

For issues or questions:
- Check XAMPP documentation: https://www.apachefriends.org/docs/
- Verify Apache and MySQL are running
- Check error logs in XAMPP Control Panel

---

## Features Summary

✅ User registration system with database storage
✅ Complete order/purchase tracking
✅ Automatic logging to text files
✅ Admin dashboard with statistics
✅ View all registrations and orders
✅ Download log files
✅ Secure log file access
✅ bKash payment integration ready

---

**Deployment Complete!**

Your Flex & Bliss e-commerce website is now running on XAMPP localhost with full backend functionality.
