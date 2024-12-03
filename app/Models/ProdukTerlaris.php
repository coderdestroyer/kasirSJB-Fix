<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProdukTerlaris extends Model
{
    protected $table = 'produk_terlaris'; // Nama view di database
    public $timestamps = false;          // View tidak memiliki kolom timestamps

    // Tambahkan jika diperlukan
    protected $fillable = ['nama_produk', 'total_terjual'];
}
