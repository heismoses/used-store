# Installation Guide — WampServer

Step-by-step instructions to install and run the UsedStore Marketplace on Windows using WampServer.

---

## Prerequisites

- Windows 10 or later
- [WampServer 3.x](https://www.wampserver.com/en/) (includes Apache, PHP, MySQL)
- A modern web browser (Chrome, Firefox, Edge)

---

## Step 1: Install WampServer

1. Download WampServer from https://www.wampserver.com/en/
2. Run the installer and follow the prompts
3. Install to the default path: `C:\wamp64`
4. Launch WampServer after installation
5. Wait until the tray icon turns **green** (all services running)

> If the icon stays orange or red, click it and select **Restart All Services**. Common fixes: close Skype/other apps using port 80, or install Visual C++ Redistributables.

---

## Step 2: Copy Project Files

1. Copy the entire project folder to:

   ```
   C:\wamp64\www\used-store-marketplace
   ```

2. The main entry point should be:

   ```
   C:\wamp64\www\used-store-marketplace\index.php
   ```

---

## Step 3: Create the Database

### Option A: phpMyAdmin (Recommended)

1. Open browser → `http://localhost/phpmyadmin`
2. Click **Import** in the top menu
3. Click **Choose File** → select `database/schema.sql` from the project folder
4. Click **Go** at the bottom
5. You should see: *Import has been successfully finished*

### Option B: MySQL Console

1. Click Wamp icon → **MySQL** → **MySQL Console**
2. Press Enter (default root password is empty)
3. Run:

   ```sql
   SOURCE C:/wamp64/www/used-store-marketplace/database/schema.sql;
   ```

---

## Step 4: Configure Database Connection

Open `config/database.php` and verify settings:

```php
define('DB_HOST', 'localhost');
define('DB_NAME', 'used_store_marketplace');
define('DB_USER', 'root');
define('DB_PASS', '');          // Empty for default WampServer
define('SITE_URL', 'http://localhost/used-store-marketplace');
```

Change `DB_PASS` if you set a MySQL root password. Update `SITE_URL` if you renamed the folder.

---

## Step 5: Set Folder Permissions

Ensure the `uploads` folder is writable:

1. Right-click `uploads` folder → **Properties** → **Security**
2. Give **Users** group **Modify** permission
3. Apply to subfolders: `uploads/products/` and `uploads/profiles/`

On WampServer this usually works by default.

---

## Step 6: Enable PHP Extensions

1. Click Wamp icon → **PHP** → **PHP Extensions**
2. Ensure these are checked:
   - `php_pdo_mysql`
   - `php_mbstring`
   - `php_gd2` (optional, for image handling)

3. Restart All Services if you changed anything

---

## Step 7: Access the Application

Open your browser and visit:

```
http://localhost/used-store-marketplace
```

### Login with demo accounts

| Role  | Email               | Password |
|-------|---------------------|----------|
| Admin | admin@usedstore.com | password |
| User  | john@example.com    | password |

---

## Troubleshooting

### "Database connection failed"

- Wamp icon must be green
- Database `used_store_marketplace` exists in phpMyAdmin
- Credentials in `config/database.php` are correct

### CSS/JS not loading

- Check `SITE_URL` in `config/database.php` matches your folder name
- Clear browser cache (Ctrl+F5)

### Images not uploading

- `uploads/products/` and `uploads/profiles/` exist and are writable
- Check PHP `upload_max_filesize` and `post_max_size` in `php.ini` (Wamp → PHP → php.ini)

### 404 on pages

- Confirm project is in `C:\wamp64\www\used-store-marketplace`
- Apache **mod_rewrite** should be enabled (Wamp → Apache → Apache Modules → rewrite_module)

### Blank white page

- Enable error display: Wamp → PHP → php.ini → set `display_errors = On`
- Check Apache error log: Wamp → Apache → Apache Error Log

---

## Optional: Virtual Host Setup

For a cleaner URL like `http://usedstore.local`:

1. Edit `C:\wamp64\bin\apache\apache2.x.x\conf\extra\httpd-vhosts.conf`:

   ```apache
   <VirtualHost *:80>
       DocumentRoot "C:/wamp64/www/used-store-marketplace"
       ServerName usedstore.local
       <Directory "C:/wamp64/www/used-store-marketplace">
           AllowOverride All
           Require all granted
       </Directory>
   </VirtualHost>
   ```

2. Add to `C:\Windows\System32\drivers\etc\hosts`:

   ```
   127.0.0.1 usedstore.local
   ```

3. Update `SITE_URL` in `config/database.php` to `http://usedstore.local`
4. Restart WampServer

---

## Verification Checklist

- [ ] Homepage loads with hero section and categories
- [ ] Can register a new user account
- [ ] Can login and access dashboard
- [ ] Can post an item with images
- [ ] Admin can approve items at `/admin/index.php`
- [ ] Marketplace shows approved items
- [ ] Search and filters work
- [ ] Messages can be sent between users
- [ ] Dark mode toggle works
- [ ] Mobile menu works on small screens

---

*Installation complete. See [PROJECT_DOCUMENTATION.md](PROJECT_DOCUMENTATION.md) for system analysis and diagrams.*
