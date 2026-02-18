# ADD TIMESTAMPS TO TRANSAKSI KK

## Perubahan:

### 1. Migration
- File: `database/migrations/2026_02_18_011053_add_timestamps_to_transaksi_kk_table.php`
- Menambahkan kolom `created_at` dan `updated_at` (nullable)
- Mengisi data existing dengan nilai dari `tanggal_kk`

### 2. Model TransaksiKk
- File: `app/Models/TransaksiKk.php`
- Mengubah `public $timestamps = false` menjadi `public $timestamps = true`

### 3. Table View
- File: `app/Filament/Resources/TransaksiKks/Tables/TransaksiKksTable.php`
- Menambahkan kolom `created_at` dan `updated_at`
- Kolom tersembunyi by default (toggleable)
- Format: d/m/Y H:i

## Keamanan Data:

✅ **AMAN** untuk data existing karena:
1. Kolom dibuat dengan `nullable()` - tidak akan error untuk data lama
2. Migration otomatis mengisi data lama dengan nilai dari `tanggal_kk`
3. Data baru akan otomatis terisi saat create/update

## Cara Deploy di Production:

```bash
cd /var/www/gas-petty-cash
git pull origin main
php artisan migrate
php artisan config:clear
php artisan view:clear
sudo systemctl restart php8.3-fpm
```

## Hasil:

- Data lama: `created_at` dan `updated_at` = `tanggal_kk`
- Data baru: `created_at` dan `updated_at` otomatis terisi oleh Laravel
- User bisa toggle kolom untuk melihat/menyembunyikan timestamps
