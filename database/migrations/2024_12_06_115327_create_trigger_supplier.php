<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerSupplier extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER after_insert_supplier
            AFTER INSERT ON supplier
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
                    'Supplier',
                    NEW.id_supplier,
                    CONCAT('Supplier baru ditambahkan: ', NEW.nama), 
                    'INSERT',
                    NULL, 
                    NEW.nama, 
                    NOW(),
                    NOW()
                );
            END;
        ");

        // Trigger after UPDATE on supplier
        DB::unprepared("
            CREATE TRIGGER after_update_supplier
            AFTER UPDATE ON supplier
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
                    'Supplier',
                    NEW.id_supplier,
                    CONCAT('Supplier diperbarui: ', OLD.nama, ' menjadi ', NEW.nama), 
                    'UPDATE',
                    OLD.nama, 
                    NEW.nama, 
                    NOW(),
                    NOW()
                );
            END;
        ");

        // Trigger after DELETE on supplier
        DB::unprepared("
            CREATE TRIGGER after_delete_supplier
            AFTER DELETE ON supplier
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
                    'Supplier',
                    OLD.id_supplier, 
                    CONCAT('Supplier dihapus: ', OLD.nama), 
                    'DELETE',
                    OLD.nama, 
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
        DB::unprepared("DROP TRIGGER IF EXISTS after_insert_supplier;");
        DB::unprepared("DROP TRIGGER IF EXISTS after_update_supplier;");
        DB::unprepared("DROP TRIGGER IF EXISTS after_delete_supplier;");
    }
}
