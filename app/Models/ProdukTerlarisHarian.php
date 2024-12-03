<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProdukTerlarisHarian extends Model
{
    protected $table = 'view_produk_terjual_harian'; // Nama view yang ada di database
    public $timestamps = false; // Tidak ada kolom timestamps di view
    protected $fillable = ['tanggal', 'nama_produk', 'merk', 'harga_jual_produk', 'total_terjual', 'total_pendapatan'];
}
