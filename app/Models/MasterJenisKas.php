<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterJenisKas extends Model
{
    protected $table = 'master_jenis_kas';
    protected $primaryKey = 'id_jenis_kas';
    public $timestamps = false;
    protected $fillable = ['id_jenis_kas', 'jenis_kas', 'status'];
}
