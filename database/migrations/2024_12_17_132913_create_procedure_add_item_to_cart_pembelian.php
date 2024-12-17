<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureAddItemToCartPembelian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared('
            CREATE PROCEDURE add_item_to_cart_pembelian(
                IN p_id_pembelian INT,
                IN p_kode_produk VARCHAR(50),
                IN p_id_supplier INT
            )
            BEGIN
                DECLARE cart_id INT;
                
                -- Cek apakah item sudah ada di keranjang
                SELECT id_cart_pembelian INTO cart_id
                FROM cart_pembelian
                WHERE id_pembelian = p_id_pembelian AND kode_produk = p_kode_produk
                LIMIT 1;

                -- Jika item sudah ada, update jumlah
                IF cart_id IS NOT NULL THEN
                    UPDATE cart_pembelian
                    SET jumlah = jumlah + 1
                    WHERE id_cart_pembelian = cart_id;
                ELSE
                    -- Jika item belum ada, tambahkan item baru
                    INSERT INTO cart_pembelian (id_supplier, id_pembelian, kode_produk, jumlah)
                    VALUES (p_id_supplier, p_id_pembelian, p_kode_produk, 1);
                END IF;
            END ;
        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('procedure_add_item_to_cart_pembelian');
    }
}
