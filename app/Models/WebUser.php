<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class WebUser extends Authenticatable implements FilamentUser
{
    use HasFactory, Notifiable;

    protected $table = 'web_users';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'permissions',
        'allowed_jenis_kas',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the name of the unique identifier for the user.
     */
    public function getAuthIdentifierName()
    {
        return 'id'; // Use primary key as identifier
    }

    /**
     * Get the unique identifier for the user.
     */
    public function getAuthIdentifier()
    {
        return $this->getKey(); // Return primary key value
    }

    /**
     * Get the password for the user.
     */
    public function getAuthPassword()
    {
        return $this->password;
    }

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'permissions' => 'array',
            'allowed_jenis_kas' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Determine if the user can access the given Filament panel.
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->is_active;
    }

    /**
     * Check if user has specific permission
     */
    public function hasPermission(string $permission): bool
    {
        if ($this->role === 'super_admin') {
            return true;
        }

        return in_array($permission, $this->permissions ?? []);
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['super_admin', 'admin']);
    }

    /**
     * Check if user is super admin
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user can access specific jenis kas
     */
    public function canAccessJenisKas($jenisKasId): bool
    {
        // Super admin can access all jenis kas
        if ($this->isSuperAdmin()) {
            return true;
        }

        // Check if user has allowed_jenis_kas set
        $allowedJenisKas = $this->allowed_jenis_kas ?? [];
        
        return in_array($jenisKasId, $allowedJenisKas);
    }

    /**
     * Get array of allowed jenis kas IDs
     */
    public function getAllowedJenisKasIds(): array
    {
        // Super admin can access all jenis kas
        if ($this->isSuperAdmin()) {
            return [1, 2, 3, 4];
        }

        return $this->allowed_jenis_kas ?? [];
    }

    /**
     * Get available jenis kas options with labels
     */
    public static function getAvailableJenisKas(): array
    {
        return [
            1 => 'Kas Kecil (KGS)',
            2 => 'Kas Operasional (OGS)',
            3 => 'Kas Personalia (PGS)',
            4 => 'Kas GS2 (GS2)',
        ];
    }

    /**
     * Get available permissions - individual menu access
     */
    public static function getAvailablePermissions(): array
    {
        return [
            'view_dashboard' => 'Dashboard',
            'view_transaksi_km' => 'Transaksi Kas Masuk',
            'view_transaksi_kk' => 'Transaksi Kas Keluar', 
            'view_laporan_mutasi' => 'Laporan Mutasi Kas',
            'view_proyeksi' => 'Proyeksi',
            'view_pengajuan_budget' => 'Pengajuan Budget',
            'view_summary_pengajuan_budget' => 'Summary Pengajuan Budget',
            'view_master_bayar' => 'Master Bayar',
            'view_master_terima' => 'Master Terima',
            'view_master_klasifikasi' => 'Master Klasifikasi',
            'view_master_jenis_kas' => 'Master Jenis Kas',
            'view_web_users' => 'User Management',
        ];
    }

    /**
     * Get available roles
     */
    public static function getAvailableRoles(): array
    {
        return [
            'super_admin' => 'Super Admin',
            'user' => 'User',
        ];
    }
}