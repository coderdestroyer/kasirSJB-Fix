<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerKategori extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER after_insert_kategori
            AFTER INSERT ON kategori
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (
                    log_time, 
                    name, 
                    log_target, 
                    log_description, 
                    activity_type, 
                    old_value, 
                    new_value, 
                    created_at, 
                    updated_at
                )
                VALUES (
                    NOW(),
                    'Kategori',
                    NEW.id_kategori,
                    CONCAT('Kategori baru ditambahkan: ', NEW.nama_kategori),
                    'INSERT',
                    NULL,
                    NEW.nama_kategori,
                    NOW(),
                    NOW()
                );
            END;
        ");

        // Trigger after UPDATE on kategori
        DB::unprepared("
            CREATE TRIGGER after_update_kategori
            AFTER UPDATE ON kategori
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (
                    log_time, 
                    name, 
                    log_target, 
                    log_description, 
                    activity_type, 
                    old_value, 
                    new_value, 
                    created_at, 
                    updated_at
                )
                VALUES (
                    NOW(),
                    'Kategori',
                    NEW.id_kategori,
                    CONCAT('Kategori diperbarui: ', OLD.nama_kategori, ' menjadi ', NEW.nama_kategori),
                    'UPDATE',
                    OLD.nama_kategori,
                    NEW.nama_kategori,
                    NOW(),
                    NOW()
                );
            END;
        ");

        // Trigger after DELETE on kategori
        DB::unprepared("
            CREATE TRIGGER after_delete_kategori
            AFTER DELETE ON kategori
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (
                    log_time, 
                    name, 
                    log_target, 
                    log_description, 
                    activity_type, 
                    old_value, 
                    new_value, 
                    created_at, 
                    updated_at
                )
                VALUES (
                    NOW(),
                    'Kategori',
                    OLD.id_kategori,
                    CONCAT('Kategori dihapus: ', OLD.nama_kategori),
                    'DELETE',
                    OLD.nama_kategori,
                    NULL,
                    NOW(),
                    NOW()
                );
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
        DB::unprepared("DROP TRIGGER IF EXISTS after_insert_kategori;");
        DB::unprepared("DROP TRIGGER IF EXISTS after_update_kategori;");
        DB::unprepared("DROP TRIGGER IF EXISTS after_delete_kategori;");
    }
}
