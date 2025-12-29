<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterKlasifikasi extends Model
{
    protected $table = 'master_klasifikasi';
    protected $primaryKey = 'id_klasifikasi';
    public $incrementing = false;
    protected $keyType = 'string';
    public $timestamps = false; // check schema?
    protected $fillable = ['id_klasifikasi', 'kriteria', 'klasifikasi', 'coa', 'tipe_klasifikasi', 'status'];
}
