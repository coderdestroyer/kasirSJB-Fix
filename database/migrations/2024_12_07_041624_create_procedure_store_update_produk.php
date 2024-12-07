<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProcedureStoreUpdateProduk extends Migration
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
            IN in_harga_beli_produk DECIMAL(10, 2),
            IN in_min_stok INT -- Parameter baru untuk min_stok
        )
        BEGIN
            DECLARE last_inserted_id INT;
            DECLARE error_occurred BOOL DEFAULT FALSE;

            -- Tangani error dengan rollback
            DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
            BEGIN
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

            -- Insert data ke tabel detail_produk dengan memasukkan min_stok
            INSERT INTO detail_produk (kode_produk, stok_produk, merk, harga_beli_produk, min_stok)
            VALUES (last_inserted_id, in_stok_produk, in_merk, in_harga_beli_produk, in_min_stok);

            -- Jika tidak ada error, commit transaksi
            IF NOT error_occurred THEN
                COMMIT;
            END IF;
        END;
        ");

        DB::unprepared("
        CREATE DEFINER=`root`@`localhost` PROCEDURE `update_produk`(
            IN p_kode_produk VARCHAR(50),
            IN p_nama_produk VARCHAR(255),
            IN p_kategori INT,
            IN p_harga_beli DECIMAL(10, 2),
            IN p_harga_jual DECIMAL(10, 2),
            IN p_merk VARCHAR(100),
            IN p_stok_produk INT,
            IN p_min_stok INT -- Parameter baru untuk min_stok
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

            -- Update tabel detail_produk dengan memperbarui min_stok
            UPDATE detail_produk
            SET 
                harga_beli_produk = p_harga_beli,
                stok_produk = p_stok_produk,
                merk = p_merk,
                min_stok = p_min_stok -- Menambahkan update untuk min_stok
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
        DB::unprepared("DROP PROCEDURE IF EXISTS store_produk;");
        DB::unprepared("DROP PROCEDURE IF EXISTS update_produk;");
    }
}
