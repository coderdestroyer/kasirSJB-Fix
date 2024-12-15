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
}
