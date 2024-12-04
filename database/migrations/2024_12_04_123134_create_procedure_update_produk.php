<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureUpdateProduk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
        CREATE DEFINER=`root`@`localhost` PROCEDURE `update_produk`(
            IN p_kode_produk VARCHAR(50),
            IN p_nama_produk VARCHAR(255),
            IN p_kategori INT,
            IN p_harga_beli DECIMAL(10, 2),
            IN p_harga_jual DECIMAL(10, 2),
            IN p_merk VARCHAR(100),
            IN p_stok_produk INT
        )
        BEGIN
            DECLARE error_occurred INT DEFAULT 0;

            -- Tangani error dengan rollback
            DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
            BEGIN
                SET error_occurred = 1;
                ROLLBACK;
            END;

            -- Mulai transaksi
            START TRANSACTION;

            -- Update tabel produk
            UPDATE produk
            SET 
                nama_produk = p_nama_produk,
                id_kategori = p_kategori,
                harga_jual = p_harga_jual
            WHERE 
                kode_produk = p_kode_produk;

            -- Update tabel detail_produk
            UPDATE detail_produk
            SET 
                harga_beli_produk = p_harga_beli,
                stok_produk = p_stok_produk,
                merk = p_merk
            WHERE 
                kode_produk = p_kode_produk;

            -- Jika tidak ada error, commit transaksi
            IF error_occurred = 0 THEN
                COMMIT;
            END IF;  
        END;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('procedure_update_produk');
    }
}
