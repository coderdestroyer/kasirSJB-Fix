<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER after_setting_insert
            AFTER INSERT ON setting
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (
                    log_time, name, log_target, log_description, activity_type, old_value, new_value, created_at, updated_at
                )
                VALUES (
                    NOW(),
                    'Admin',
                    'setting',
                    CONCAT('Inserted new setting with ID: ', NEW.id_setting),
                    'INSERT',
                    NULL,
                    CONCAT(
                        'nama_perusahaan: ', NEW.nama_perusahaan, ', ',
                        'alamat: ', NEW.alamat, ', ',
                        'telepon: ', NEW.telepon, ', ',
                        'tipe_nota: ', NEW.tipe_nota, ', ',
                        'path_logo: ', NEW.path_logo
                    ),
                    NOW(),
                    NOW()
                );
            END
        ");

        DB::unprepared("
            CREATE TRIGGER after_setting_update
            AFTER UPDATE ON setting
            FOR EACH ROW
            BEGIN
                INSERT INTO log_activity (
                    log_time, name, log_target, log_description, activity_type, old_value, new_value, created_at, updated_at
                )
                VALUES (
                    NOW(),
                    'Admin',
                    'setting',
                    CONCAT('Updated setting with ID: ', NEW.id_setting),
                    'UPDATE',
                    CONCAT(
                        'nama_perusahaan: ', OLD.nama_perusahaan, ', ',
                        'alamat: ', OLD.alamat, ', ',
                        'telepon: ', OLD.telepon, ', ',
                        'tipe_nota: ', OLD.tipe_nota, ', ',
                        'path_logo: ', OLD.path_logo
                    ),
                    CONCAT(
                        'nama_perusahaan: ', NEW.nama_perusahaan, ', ',
                        'alamat: ', NEW.alamat, ', ',
                        'telepon: ', NEW.telepon, ', ',
                        'tipe_nota: ', NEW.tipe_nota, ', ',
                        'path_logo: ', NEW.path_logo
                    ),
                    NOW(),
                    NOW()
                );
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
        DB::unprepared("DROP TRIGGER IF EXISTS after_setting_insert");
        DB::unprepared("DROP TRIGGER IF EXISTS after_setting_update");
    }
}
