# DEBUG PRODUCTION ERROR - Pengajuan Budget

## Error yang Terjadi:
```
POST http://10.10.1.17:8081/livewire/update 500 (Internal Server Error)
```

Error terjadi saat:
- Blur event pada field (kemungkinan field nominal)
- Open dropdown select (kemungkinan dropdown klasifikasi)

## Kemungkinan Penyebab:

### 1. Method `getAllowedJenisKasIds()` Tidak Ditemukan
Karena kita baru menambahkan method ini di WebUser model, kemungkinan di server production belum ter-update.

### 2. Cache Belum Di-clear
Cache config/route/view masih menggunakan code lama.

### 3. Composer Autoload Belum Di-refresh
Class baru belum ter-load.

## Solusi untuk Server Production:

### Step 1: Pull Latest Code
```bash
cd /var/www/gas-petty-cash
git pull origin main
```

### Step 2: Run Migration (jika belum)
```bash
php artisan migrate
```

### Step 3: Run Seeder (jika belum)
```bash
php artisan db:seed --class=UpdateWebUsersJenisKasSeeder
```

### Step 4: Clear All Cache
```bash
php artisan config:clear
php artisan cache:clear
php artisan view:clear
php artisan route:clear
```

### Step 5: Refresh Composer Autoload
```bash
composer dump-autoload
```

### Step 6: Restart PHP-FPM (jika menggunakan)
```bash
sudo systemctl restart php8.2-fpm
# atau
sudo systemctl restart php8.3-fpm
# sesuaikan dengan versi PHP yang digunakan
```

### Step 7: Check Laravel Log
```bash
tail -f storage/logs/laravel.log
```

Atau buka file log untuk melihat error detail:
```bash
cat storage/logs/laravel.log | tail -100
```

## Jika Masih Error:

### Cek Error Detail di Log
Buka file: `storage/logs/laravel.log` di server production

Cari error terakhir yang berisi:
- Stack trace
- Error message
- File dan line number

### Kemungkinan Error Spesifik:

#### Error 1: Method getAllowedJenisKasIds() not found
**Solusi:**
```bash
# Pastikan file WebUser.php sudah ter-update
cat app/Models/WebUser.php | grep "getAllowedJenisKasIds"

# Jika tidak ada, pull lagi
git pull origin main
composer dump-autoload
```

#### Error 2: Column allowed_jenis_kas not found
**Solusi:**
```bash
# Run migration
php artisan migrate

# Run seeder
php artisan db:seed --class=UpdateWebUsersJenisKasSeeder
```

#### Error 3: Syntax Error di PengajuanBudgetResource
**Solusi:**
```bash
# Cek syntax
php artisan about

# Jika ada error, lihat detail
php -l app/Filament/Resources/PengajuanBudgetResource.php
```

## Quick Fix Sementara:

Jika error masih terjadi dan urgent, bisa temporary disable filtering di PengajuanBudgetResource:

### File: `app/Filament/Resources/PengajuanBudgetResource.php`

**Comment bagian ini di form Select id_jenis_kas:**
```php
Select::make('id_jenis_kas')
    ->label('Jenis Kas')
    ->options(MasterJenisKas::where('status', 0)->pluck('jenis_kas', 'id_jenis_kas'))
    // ->options(function () {
    //     $user = auth()->user();
    //     if ($user && !$user->isSuperAdmin()) {
    //         $allowedIds = $user->getAllowedJenisKasIds();
    //         return MasterJenisKas::where('status', 0)
    //             ->whereIn('id_jenis_kas', $allowedIds)
    //             ->pluck('jenis_kas', 'id_jenis_kas');
    //     }
    //     return MasterJenisKas::where('status', 0)->pluck('jenis_kas', 'id_jenis_kas');
    // })
    ->required()
    ->searchable()
    ->native(false),
```

**Comment bagian ini di table:**
```php
// Comment bagian modifyQueryUsing
// $user = auth()->user();
// if ($user && !$user->isSuperAdmin()) {
//     $allowedJenisKas = $user->getAllowedJenisKasIds();
//     if (!empty($allowedJenisKas)) {
//         $table->modifyQueryUsing(fn ($query) => $query->whereIn('id_jenis_kas', $allowedJenisKas));
//     }
// }
```

Setelah itu:
```bash
php artisan config:clear
php artisan cache:clear
```

## Cara Cek Error Lebih Detail:

### 1. Enable Debug Mode (HATI-HATI - hanya untuk testing)
Edit `.env` di server:
```
APP_DEBUG=true
```

Refresh halaman, lihat error detail, lalu kembalikan:
```
APP_DEBUG=false
```

### 2. Cek PHP Error Log
```bash
tail -f /var/log/php8.2-fpm.log
# atau
tail -f /var/log/nginx/error.log
```

### 3. Test di Artisan Tinker
```bash
php artisan tinker

# Test method
$user = App\Models\WebUser::first();
$user->getAllowedJenisKasIds();
```

## Checklist Troubleshooting:

- [ ] Git pull latest code
- [ ] Run migration
- [ ] Run seeder
- [ ] Clear all cache
- [ ] Composer dump-autoload
- [ ] Restart PHP-FPM
- [ ] Check laravel.log
- [ ] Test dengan user super admin
- [ ] Test dengan user biasa

## Contact Info:
Jika masih error, kirim screenshot dari:
1. Error message di browser console
2. Isi file `storage/logs/laravel.log` (100 baris terakhir)
3. Output dari `php artisan about`
