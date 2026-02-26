# Perubahan: KAS BANGUNAN → KAS GS2

## Ringkasan
Mengubah nama "KAS BANGUNAN" menjadi "KAS GS2" dan prefix dokumen dari "BGS" menjadi "GS2" di seluruh aplikasi.

## Perubahan Database
- **master_jenis_kas**: `KAS BANGUNAN` → `KAS GS2`
- **transaksi_km**: Prefix `KM-BGS-` → `KM-GS2-`
- **transaksi_kk**: Prefix `KK-BGS-` → `KK-GS2-`

## File yang Diubah

### Models
1. `app/Models/TransaksiKm.php`
   - Semua mapping `4 => 'BGS'` → `4 => 'GS2'`
   - Method: getNextTransactionNumber, getPreviewNumberForEdit, boot (creating & updating)

2. `app/Models/TransaksiKk.php`
   - Semua mapping `4 => 'BGS'` → `4 => 'GS2'`
   - Method: getNextTransactionNumber, getPreviewNumberForEdit, boot (creating & updating)

3. `app/Models/SummaryPengajuanBudget.php`
   - getSummaryData: `case 'KAS BANGUNAN'` → `case 'KAS GS2'`

4. `app/Models/WebUser.php`
   - getAvailableJenisKas: `'Kas Bangunan (BGS)'` → `'Kas GS2 (GS2)'`

### Filament Resources
5. `app/Filament/Resources/TransaksiKms/Tables/TransaksiKmsTable.php`
   - Filter: `'Kas Bangunan (BGS)'` → `'Kas GS2 (GS2)'`

6. `app/Filament/Resources/TransaksiKms/Schemas/TransaksiKmForm.php`
   - Select options: `'Kas Bangunan (BGS)'` → `'Kas GS2 (GS2)'`

7. `app/Filament/Resources/TransaksiKks/Tables/TransaksiKksTable.php`
   - Filter: `'Kas Bangunan (BGS)'` → `'Kas GS2 (GS2)'`

8. `app/Filament/Resources/TransaksiKks/Schemas/TransaksiKkForm.php`
   - Select options: `'kas Bangunan (BGS)'` → `'Kas GS2 (GS2)'`

9. `app/Filament/Resources/SummaryPengajuanBudgetResource.php`
   - Label: `'Kas Bangunan'` → `'Kas GS2'`
   - Field `bgs` tetap digunakan (tidak diubah nama column)

### Views
10. `resources/views/exports/summary-pengajuan-budget.blade.php`
    - Label: `KAS BANGUNAN` → `KAS GS2`

### Migration
11. `database/migrations/2026_02_26_014841_update_kas_bangunan_to_kas_gs2.php`
    - Update master_jenis_kas
    - Update prefix transaksi_km dan transaksi_kk
    - Rollback support

## Format Nomor Dokumen
- **Kas Masuk**: `KM-GS2-YYMM-XXXX` (contoh: KM-GS2-2602-0001)
- **Kas Keluar**: `KK-GS2-YYMM-XXXX` (contoh: KK-GS2-2602-0001)

## Catatan
- Column `bgs` di tabel `summary_pengajuan_budget` TIDAK diubah namanya untuk menghindari breaking change
- Hanya label/display yang diubah menjadi "KAS GS2"
- Semua perubahan konsisten di seluruh aplikasi
- Migration dapat di-rollback jika diperlukan

## Testing
Setelah update, pastikan:
1. ✅ Master jenis kas menampilkan "KAS GS2"
2. ✅ Form transaksi KM/KK menampilkan "Kas GS2 (GS2)"
3. ✅ Nomor dokumen baru menggunakan prefix GS2
4. ✅ Filter dan laporan menampilkan "KAS GS2"
5. ✅ Summary pengajuan budget menampilkan "Kas GS2"
