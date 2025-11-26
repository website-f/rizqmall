# Production Server Storage Fix Guide

## Problem Summary
Uploaded files go to `storage/app/public` but cannot be accessed via browser because:
1. Wrong filesystem disk configuration
2. Incorrect APP_URL
3. Broken or missing storage symlink

---

## Step-by-Step Fix for Production Server

### **Step 1: Backup Your .env File**
```bash
cp .env .env.backup
```

### **Step 2: Update .env Configuration**

Open your `.env` file and make these changes:

#### Change 1: Fix FILESYSTEM_DISK
```bash
# BEFORE:
FILESYSTEM_DISK=local

# AFTER:
FILESYSTEM_DISK=public
```

#### Change 2: Fix APP_URL (use your actual production URL)
```bash
# BEFORE:
APP_URL=http://localhost

# AFTER (example - use YOUR actual domain):
APP_URL=https://yourdomain.com
# OR if using IP:
APP_URL=http://your-server-ip
# OR if using port:
APP_URL=http://yourdomain.com:8001
```

#### Change 3: Update Session Settings (for consistency)
```bash
# BEFORE:
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_DOMAIN=null

# AFTER:
SESSION_LIFETIME=43200
SESSION_ENCRYPT=true
SESSION_DOMAIN=yourdomain.com  # or your actual domain
```

---

### **Step 3: Remove Old Broken Symlink**

**For Linux/Ubuntu Production Server:**
```bash
# Navigate to your project directory
cd /path/to/your/rizqmall

# Remove the old symlink
rm -rf public/storage
```

**For Windows Server:**
```powershell
# Navigate to your project directory
cd C:\path\to\your\rizqmall

# Remove the old symlink
Remove-Item "public\storage" -Force -Recurse
```

---

### **Step 4: Recreate the Storage Symlink**

```bash
php artisan storage:link
```

**Expected Output:**
```
INFO  The [/path/to/public/storage] link has been connected to [/path/to/storage/app/public].
```

---

### **Step 5: Clear All Caches**

```bash
# Clear configuration cache
php artisan config:clear

# Clear application cache
php artisan cache:clear

# Clear route cache (optional but recommended)
php artisan route:clear

# Clear view cache (optional but recommended)
php artisan view:clear
```

---

### **Step 6: Verify the Symlink Works**

**For Linux:**
```bash
# Check if symlink exists
ls -la public/storage

# Check if you can see your uploaded files
ls -la public/storage/products
ls -la public/storage/stores
```

**For Windows:**
```powershell
# Check if symlink exists
Get-Item "public\storage" | Select-Object LinkType, Target

# Check if you can see your uploaded files
Get-ChildItem "public\storage\products"
Get-ChildItem "public\storage\stores"
```

---

### **Step 7: Test URL Generation**

```bash
php artisan tinker
```

Then in tinker:
```php
echo asset('storage/products/test.jpg');
// Should output: https://yourdomain.com/storage/products/test.jpg

exit
```

---

### **Step 8: Set Correct Permissions (Linux Only)**

```bash
# Set ownership (replace 'www-data' with your web server user)
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache

# Set permissions
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

**Common web server users:**
- Apache: `www-data` (Ubuntu/Debian) or `apache` (CentOS/RHEL)
- Nginx: `www-data` or `nginx`

---

### **Step 9: Restart Your Web Server (if needed)**

**For Apache:**
```bash
sudo systemctl restart apache2
# OR
sudo service apache2 restart
```

**For Nginx:**
```bash
sudo systemctl restart nginx
# OR
sudo service nginx restart
```

**For PHP-FPM:**
```bash
sudo systemctl restart php8.2-fpm
# (adjust version number to your PHP version)
```

---

## Verification Checklist

After completing all steps, verify:

- [ ] `.env` has `FILESYSTEM_DISK=public`
- [ ] `.env` has correct `APP_URL` with your domain
- [ ] `public/storage` symlink exists and points to `storage/app/public`
- [ ] Files in `storage/app/public/products` are accessible via `public/storage/products`
- [ ] Images display correctly in your Blade views
- [ ] No 404 errors when accessing image URLs

---

## Quick Command Summary for Production

```bash
# 1. Backup
cp .env .env.backup

# 2. Edit .env (use nano, vim, or your preferred editor)
nano .env
# Change: FILESYSTEM_DISK=public
# Change: APP_URL=https://yourdomain.com

# 3. Remove old symlink
rm -rf public/storage

# 4. Create new symlink
php artisan storage:link

# 5. Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

# 6. Set permissions (Linux only)
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 7. Restart web server (if needed)
sudo systemctl restart nginx
# OR
sudo systemctl restart apache2
```

---

## Troubleshooting

### Issue: "The [public/storage] link already exists"
```bash
# Force remove and recreate
rm -rf public/storage
php artisan storage:link
```

### Issue: Images still not showing
1. Check browser console for 404 errors
2. Verify the actual file path in database matches the file location
3. Hard refresh browser (Ctrl + Shift + R)
4. Check file permissions (Linux)

### Issue: Permission denied errors
```bash
# Linux - fix permissions
sudo chown -R www-data:www-data storage
sudo chmod -R 775 storage
```

---

## What Changed in Your Local Environment

1. ✅ `.env`: `FILESYSTEM_DISK=local` → `FILESYSTEM_DISK=public`
2. ✅ `.env`: `APP_URL=http://localhost` → `APP_URL=http://localhost:8001`
3. ✅ `.env`: `SESSION_LIFETIME=120` → `SESSION_LIFETIME=43200`
4. ✅ `.env`: `SESSION_ENCRYPT=false` → `SESSION_ENCRYPT=true`
5. ✅ `.env`: `SESSION_DOMAIN=null` → `SESSION_DOMAIN=localhost`
6. ✅ Removed old symlink: `public/storage`
7. ✅ Created new symlink: `php artisan storage:link`
8. ✅ Cleared config cache: `php artisan config:clear`
9. ✅ Cleared app cache: `php artisan cache:clear`

---

## Notes

- Always backup your `.env` before making changes
- Use HTTPS in production (`APP_URL=https://yourdomain.com`)
- Ensure your web server user has proper permissions
- Test thoroughly after deployment
- Consider using a deployment script to automate these steps
