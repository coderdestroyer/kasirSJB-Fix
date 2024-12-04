<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureStoreProduk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
        CREATE DEFINER=`root`@`localhost` PROCEDURE `store_produk`(
            IN in_nama_produk VARCHAR(255),
            IN in_harga_jual DECIMAL(10, 2),
            IN in_id_kategori INT,
            IN in_stok_produk INT,
            IN in_merk VARCHAR(255),
            IN in_harga_beli_produk DECIMAL(10, 2)
        )
        BEGIN
            DECLARE last_inserted_id INT;
            DECLARE error_occurred BOOL DEFAULT FALSE;

            -- Tangani error dengan rollback
            DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
            BEGIN
                -- Tandai bahwa terjadi error
                SET error_occurred = TRUE;
                ROLLBACK;
            END;

            -- Mulai transaksi
            START TRANSACTION;

            -- Insert data ke tabel produk
            INSERT INTO produk (nama_produk, harga_jual, id_kategori)
            VALUES (in_nama_produk, in_harga_jual, in_id_kategori);

            -- Dapatkan ID yang baru saja di-insert
            SET last_inserted_id = LAST_INSERT_ID();

            -- Insert data ke tabel detail_produk
            INSERT INTO detail_produk (kode_produk, stok_produk, merk, harga_beli_produk)
            VALUES (last_inserted_id, in_stok_produk, in_merk, in_harga_beli_produk);

            -- Jika tidak ada error, commit transaksi
            IF NOT error_occurred THEN
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
        DB::unprepared("DROP PROCEDURE IF EXISTS 'store_produk';");
    }
}
