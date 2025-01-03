<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $table = 'pembelian';
    protected $primaryKey = 'id_pembelian';
    protected $guarded = [];

    public function supplier()
    {
        return $this->belongsTo(Supplier::class, 'id_supplier', 'id_supplier');
    }

        public function detilPembelian()
    {
        return $this->hasMany(PembelianDetail::class, 'id_pembelian');
    }

    // Hubungan dengan model CartPembelian
    public function cartPembelian()
    {
        return $this->hasMany(CartPembelian::class, 'id_pembelian');
    }
}
