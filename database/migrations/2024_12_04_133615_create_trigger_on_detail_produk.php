<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerOnDetailProduk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER log_detail_produk_after_delete
            AFTER DELETE ON detail_produk
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (log_time, name, log_target, log_description, activity_type, old_value, new_value)
                VALUES (NOW(), 'System', 'detail_produk', CONCAT('Delete detail produk: ', OLD.kode_produk), 'delete', 
                        CONCAT('Stok: ', OLD.stok_produk, ', Harga Beli: ', OLD.harga_beli_produk), NULL);
            END
        ");

        DB::unprepared("
            CREATE TRIGGER log_detail_produk_after_insert
            AFTER INSERT ON detail_produk
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (log_time, name, log_target, log_description, activity_type, old_value, new_value)
                VALUES (NOW(), 'System', 'detail_produk', CONCAT('Insert detail produk: ', NEW.kode_produk), 'insert', NULL,
                        CONCAT('Kode Produk: ', NEW.kode_produk, ', Stok: ', NEW.stok_produk, ', Harga Beli: ', NEW.harga_beli_produk));
            END
        ");

        DB::unprepared("
            CREATE TRIGGER log_detail_produk_after_update
            AFTER UPDATE ON detail_produk
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (log_time, name, log_target, log_description, activity_type, old_value, new_value)
                VALUES (NOW(), 'System', 'detail_produk', CONCAT('Update detail produk: ', OLD.kode_produk), 'update', 
                        CONCAT('Stok Lama: ', OLD.stok_produk, ', Harga Beli Lama: ', OLD.harga_beli_produk),
                        CONCAT('Stok Baru: ', NEW.stok_produk, ', Harga Beli Baru: ', NEW.harga_beli_produk));
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
        DB::unprepared("DROP TRIGGER IF EXISTS log_detail_produk_after_delete");
        DB::unprepared("DROP TRIGGER IF EXISTS log_detail_produk_after_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS log_detail_produk_after_update");
    }
}
