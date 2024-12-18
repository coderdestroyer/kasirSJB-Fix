<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureAddItemToCart extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
        CREATE PROCEDURE add_item_to_cart(
            IN p_id_user INT,
            IN p_kode_produk VARCHAR(255),
            IN p_jumlah INT
        )
        BEGIN
            DECLARE existing_cart_id INT;

            -- Periksa apakah produk sudah ada di cart user
            SELECT id_cart INTO existing_cart_id
            FROM cart
            WHERE id_user = p_id_user AND kode_produk = p_kode_produk;

            IF existing_cart_id IS NOT NULL THEN
                -- Jika sudah ada, update jumlah produk
                UPDATE cart
                SET jumlah = jumlah + p_jumlah
                WHERE id_cart = existing_cart_id;
            ELSE
                -- Jika belum ada, tambahkan ke cart
                INSERT INTO cart (id_user, kode_produk, jumlah, created_at, updated_at)
                VALUES (p_id_user, p_kode_produk, p_jumlah, NOW(), NOW());
            END IF;
        END
    ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::unprepared("DROP PROCEDURE IF EXISTS add_item_to_cart");
    }
}
