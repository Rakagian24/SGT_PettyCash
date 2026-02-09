# FIX PRODUCTION ERROR

## Error yang Terjadi:
```
Class "Laravel\Pail\PailServiceProvider" not found
```

## Solusi:

### Step 1: Install Missing Package
```bash
cd /var/www/gas-petty-cash
composer install --no-dev --optimize-autoloader
```

### Step 2: Jika Masih Error, Remove Pail dari Config
Edit file `config/app.php` atau jalankan:

```bash
# Cek apakah Pail ada di bootstrap/providers.php
cat bootstrap/providers.php | grep -i pail

# Jika ada, edit file tersebut dan hapus baris yang mengandung PailServiceProvider
nano bootstrap/providers.php
```

### Step 3: Clear Cache Lagi
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 4: Composer Dump Autoload
```bash
composer dump-autoload --optimize
```

### Step 5: Set Permissions (untuk cache error)
```bash
sudo chown -R www-data:www-data storage
sudo chown -R www-data:www-data bootstrap/cache
sudo chmod -R 775 storage
sudo chmod -R 775 bootstrap/cache
```

### Step 6: Restart Services
```bash
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

## Alternative: Install Pail Package
Jika memang diperlukan:
```bash
composer require laravel/pail --dev
```

Lalu:
```bash
composer dump-autoload
php artisan config:clear
```

## Test Aplikasi
Setelah semua selesai, test:
```bash
php artisan about
```

Jika tidak ada error, aplikasi sudah siap.
