<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterBayar extends Model
{
    protected $table = 'master_bayar';
    protected $primaryKey = 'id_bayar';
    public $timestamps = false;
    protected $fillable = ['id_bayar', 'jenis_bayar', 'status'];
}
