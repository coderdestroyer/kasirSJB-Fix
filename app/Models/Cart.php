<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    use HasFactory;

    protected $table = 'cart';
    protected $fillable = ['id_user', 'kode_produk', 'jumlah'];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'kode_produk', 'kode_produk');
    }
}
