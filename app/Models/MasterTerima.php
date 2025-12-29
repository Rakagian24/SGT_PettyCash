<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterTerima extends Model
{
    protected $table = 'master_terima';
    protected $primaryKey = 'id_terima';
    public $timestamps = false;
    protected $fillable = ['id_terima', 'jenis_terima', 'status'];
}
