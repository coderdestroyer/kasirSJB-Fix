<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CheckMinStokProduk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $results = DB::select("
            SELECT 
                p.nama_produk AS nama_produk, 
                dp.stok_produk AS stok_produk, 
                dp.min_stok AS min_stok
            FROM tokoku5.produk p
            JOIN tokoku5.detail_produk dp ON p.kode_produk = dp.kode_produk
            WHERE dp.stok_produk < dp.min_stok
        ");

        // Kamu bisa melakukan sesuatu dengan $results, seperti menyimpannya ke tabel lain atau log
        // Misalnya, kamu ingin menampilkan hasilnya
        foreach ($results as $row) {
            // Kamu bisa memproses atau menyimpan data yang ditemukan
            // Contoh: dd($row); // Tampilkan hasil query
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Tidak perlu melakukan apa-apa di sini jika hanya menjalankan query
    }
}
