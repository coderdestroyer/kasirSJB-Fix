<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerOnProduk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER log_produk_after_delete
            AFTER DELETE ON produk
            FOR EACH ROW
            BEGIN
                -- Menambahkan log aktivitas untuk delete produk
                INSERT INTO log_activity (log_time, name, log_target, log_description, activity_type, old_value, new_value)
                VALUES (NOW(), 'System', 'produk', CONCAT('Delete produk: ', OLD.nama_produk), 'delete', 
                        CONCAT('Nama Produk: ', OLD.nama_produk, ', Harga Jual: ', OLD.harga_jual), NULL);
            END
        ");

        DB::unprepared("
            CREATE TRIGGER log_produk_after_insert
            AFTER INSERT ON produk
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (log_time, name, log_target, log_description, activity_type, old_value, new_value)
                VALUES (NOW(), 'System', 'produk', 'Insert produk baru', 'insert', NULL, CONCAT('ID Produk: ', NEW.kode_produk, ', Nama: ', NEW.nama_produk));
            END
        ");

        DB::unprepared("
            CREATE TRIGGER log_produk_after_update
            AFTER UPDATE ON produk
            FOR EACH ROW
            BEGIN
                -- Menambahkan log aktivitas untuk update produk
                INSERT INTO log_activity (log_time, name, log_target, log_description, activity_type, old_value, new_value)
                VALUES (NOW(), 'System', 'produk', CONCAT('Update produk: ', OLD.nama_produk), 'update', 
                        CONCAT('Nama Produk Lama: ', OLD.nama_produk, ', Harga Jual Lama: ', OLD.harga_jual), 
                        CONCAT('Nama Produk Baru: ', NEW.nama_produk, ', Harga Jual Baru: ', NEW.harga_jual));
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
        DB::unprepared("DROP TRIGGER IF EXISTS log_produk_after_delete");
        DB::unprepared("DROP TRIGGER IF EXISTS log_produk_after_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS log_produk_after_update");
    }
}
