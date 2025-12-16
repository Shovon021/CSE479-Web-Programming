# Flex & Bliss - Complete XAMPP Setup Guide

A premium e-commerce website for handcrafted items built with React + PHP + MySQL.

---

## 📋 Prerequisites

Before you begin, make sure you have:

- **XAMPP** installed (includes Apache, MySQL, PHP)
  - Download: https://www.apachefriends.org/download.html
- **Node.js** (v18+) for React development
  - Download: https://nodejs.org/

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Link Project to XAMPP

**Option A: Symbolic Link (Recommended)**

Open **Command Prompt as Administrator** and run:

```cmd
mklink /D "C:\xampp\htdocs\FinalWeb(HTML)" "C:\Users\HP\Desktop\FinalWeb(HTML)"
```

**Option B: Copy Files**

Copy the entire `FinalWeb(HTML)` folder to `C:\xampp\htdocs\`

---

### Step 2: Start XAMPP Services

1. Open **XAMPP Control Panel**
2. Start **Apache** (click Start button)
3. Start **MySQL** (click Start button)
4. Both should show green "Running" status

---

### Step 3: Create Database

1. Open browser: http://localhost/phpmyadmin
2. Click **"New"** in the left sidebar
3. Enter database name: `flexbliss_db`
4. Click **Create**

---

### Step 4: Import Database Tables

1. In phpMyAdmin, select `flexbliss_db` database
2. Click **Import** tab
3. Click **Choose File** and select:
   ```
   C:\Users\HP\Desktop\FinalWeb(HTML)\flexbliss_deploy_FULL.sql
   ```
4. Click **Import** button at bottom
5. You should see "Import has been successfully finished"

---

### Step 5: Verify Backend Connection

Open browser and visit:
```
http://localhost/FinalWeb(HTML)/api/test_connection.php
```

You should see:
```json
{
  "success": true,
  "message": "Database connection successful!",
  "database": "flexbliss_db",
  "stats": {
    "products": 10,
    "users": 0,
    "orders": 0
  }
}
```

---

### Step 6: Start React Frontend

Open terminal in the project folder and run:

```bash
cd frontend-react
npm install      # First time only
npm run dev
```

Frontend will be available at: **http://localhost:5173**

---

## 🎯 Test the Website

| Feature | How to Test |
|---------|-------------|
| **Browse Products** | Go to `/shop` |
| **Add to Cart** | Click "Add to Cart" on any product |
| **Register/Login** | Go to `/register` or `/login` |
| **Checkout** | Add items to cart, go to `/checkout` |
| **Admin Panel** | Go to `/admin` |
| **Dark Mode** | Click Sun/Moon icon in navbar |
| **Mobile Menu** | Resize browser < 768px |

---

## 📁 Project Structure

```
FinalWeb(HTML)/
├── api/                    # PHP API endpoints
│   ├── get_products.php    # Fetch all products
│   ├── submit_order.php    # Place orders
│   ├── login.php           # User login
│   ├── register_user.php   # User registration
│   └── ...
├── includes/               # PHP core files
│   ├── config.php          # Database settings
│   ├── db.php              # Database connection
│   └── auth.php            # Authentication functions
├── frontend-react/         # React application
│   ├── src/
│   │   ├── components/     # UI components
│   │   ├── context/        # React contexts (Cart, Auth, Theme)
│   │   ├── pages/          # Page components
│   │   └── App.jsx         # Main app
│   └── vite.config.js      # Vite configuration (proxy settings)
├── uploads/                # Product images
├── logs/                   # Application logs
└── flexbliss_deploy_FULL.sql  # Complete database dump
```

---

## ⚙️ Configuration

### Database Settings

File: `includes/config.php`

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'flexbliss_db');
define('DB_USER', 'root');
define('DB_PASS', '');  // Default XAMPP has no password
```

### API Proxy (Vite)

File: `frontend-react/vite.config.js`

```javascript
proxy: {
  '/api': {
    target: 'http://localhost/FinalWeb(HTML)/api',
    changeOrigin: true,
    rewrite: (path) => path.replace(/^\/api/, ''),
  }
}
```

---

## 🛠️ Troubleshooting

### "404 Not Found" on API calls

- Ensure XAMPP Apache is running
- Verify the symbolic link exists in `C:\xampp\htdocs\`
- Check the folder name matches exactly: `FinalWeb(HTML)`

### "Database connection failed"

- Ensure XAMPP MySQL is running
- Create `flexbliss_db` database in phpMyAdmin
- Import the SQL file

### React dev server won't start

```bash
cd frontend-react
rm -rf node_modules
npm install
npm run dev
```

### CORS errors in browser

The Vite proxy should handle this. Make sure you're accessing the site at `http://localhost:5173` (not directly opening HTML files).

---

## 🌐 Production Deployment

To build for production:

```bash
cd frontend-react
npm run build
```

This creates a `dist/` folder. Copy its contents to your web server.

For the backend, upload the entire project to your PHP hosting (excluding `frontend-react/node_modules`).

---

## 📞 Features Overview

### Customer Features
- Product browsing with filters and sorting
- Search functionality
- Shopping cart with persistent storage
- Wishlist
- Product comparison
- User registration and login
- Order placement with multiple payment options
- Coupon codes
- Dark mode

### Admin Features
- Dashboard with analytics
- Product management (CRUD)
- Order management
- User management

### Payment Methods
- Cash on Delivery
- bKash (mobile banking)
- Nagad (mobile banking)
- Card Payment (UI only)

---

## 📝 Default Admin Account

After importing the database, you can create an admin account or update an existing user's role in phpMyAdmin:

```sql
UPDATE users SET role = 'admin' WHERE email = 'your-email@example.com';
```

---

## 🎨 Customization

### Changing Theme Colors

Edit: `frontend-react/src/index.css`

```css
:root {
  --color-primary: #7c3aed;      /* Purple */
  --color-accent: #ec4899;        /* Pink */
  --color-background: #ffffff;
}
```

### Adding New Products

1. Go to `/admin/products`
2. Click "Add Product"
3. Fill in details and save

Or via phpMyAdmin:
```sql
INSERT INTO products (name, description, price, category, image) 
VALUES ('Product Name', 'Description', 499.00, 'jewelry', '/uploads/image.jpg');
```

---

## ✅ Checklist Before Going Live

- [ ] Change database password in `config.php`
- [ ] Update `SITE_URL` in `config.php`
- [ ] Set `ENVIRONMENT` to `'production'` in `config.php`
- [ ] Build React app (`npm run build`)
- [ ] Test all features
- [ ] Backup database

---

**Happy Selling! 🎉**
