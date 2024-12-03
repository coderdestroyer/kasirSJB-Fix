<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StokLow extends Model
{
    protected $table = 'stok_low'; // Nama view di database
    public $timestamps = false;   // View biasanya tidak memiliki kolom timestamps
}

