<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTriggerDeleteUser extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::unprepared("
            CREATE TRIGGER log_after_delete_user AFTER DELETE ON users
            FOR EACH ROW
            BEGIN
                DECLARE kasir_alamat TEXT DEFAULT 'Tidak ditemukan';
                DECLARE kasir_nomor_hp TEXT DEFAULT 'Tidak ditemukan';

                -- Cek jika ada masalah saat mengambil data dari tabel kasir berdasarkan nama user
                BEGIN
                    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
                        SET kasir_alamat = 'Gagal ambil data', kasir_nomor_hp = 'Gagal ambil data';

                    SELECT alamat, nomor_hp INTO kasir_alamat, kasir_nomor_hp
                    FROM kasir
                    WHERE kasir.name = OLD.name
                    LIMIT 1;
                END;

                -- Masukkan data ke log_activity
                BEGIN
                    DECLARE CONTINUE HANDLER FOR SQLEXCEPTION
                        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error saat menyimpan data ke log_activity';

                    INSERT INTO log_activity (
                        log_time, 
                        name, 
                        log_target, 
                        log_description, 
                        activity_type, 
                        old_value, 
                        created_at, 
                        updated_at
                    )
                    VALUES (
                        NOW(), -- waktu log
                        OLD.name, -- nama user yang dihapus
                        'users', -- target log (nama tabel)
                        CONCAT('User dengan ID ', OLD.id, ' telah dihapus.'), -- deskripsi log
                        'delete', -- tipe aktivitas
                        CONCAT(
                            '{\"nama\": \"', OLD.name, '\", ',
                            '\"email\": \"', OLD.email, '\", ',
                            '\"alamat\": \"', kasir_alamat, '\", ',
                            '\"nomor_hp\": \"', kasir_nomor_hp, '\"}'
                        ), -- nilai lama dalam format JSON
                        NOW(), -- waktu dibuat
                        NOW() -- waktu diupdate
                    );
                END;
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
        DB::unprepared("DROP TRIGGER IF EXISTS log_after_delete_user");
    }
}
