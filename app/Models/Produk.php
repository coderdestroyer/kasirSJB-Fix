<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    use HasFactory;

    protected $table = 'produk';
    protected $fillable = [
        'kode_produk', 
        'nama_produk', 
        'harga_jual', 
        'id_kategori'
    ];

    protected $primaryKey = 'kode_produk';

    public function detailProduk()
    {
        return $this->hasOne(DetailProduk::class, 'kode_produk', 'kode_produk');
    }

    public function kategori()
    {
        return $this->belongsTo(Kategori::class, 'id_kategori', 'id_kategori');
    }
}