<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CartPembelian extends Model
{
    use HasFactory;
    protected $table = 'cart_pembelian';
    protected $primaryKey = 'id_cart_pembelian';
    public $incrementing = true;
    protected $fillable = ['id_supplier', 'id_pembelian', 'kode_produk', 'uom', 'jumlah'];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
    // Hubungan dengan model Pembelian
    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'id_pembelian', 'id_pembelian');
    }

    // Hubungan dengan model Supplier
    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }
}
