<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateFunctionHitungTotalHarga extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('
            CREATE FUNCTION calculate_total_harga(user_id INT) 
            RETURNS DECIMAL(10,2)
            DETERMINISTIC
            BEGIN
                DECLARE total DECIMAL(10,2);
                DECLARE item_harga DECIMAL(10,2);
                DECLARE item_jumlah INT;
                DECLARE done INT DEFAULT FALSE;
                DECLARE cart_cursor CURSOR FOR
                    SELECT p.harga_jual, c.jumlah 
                    FROM cart c
                    JOIN produk p ON c.kode_produk = p.kode_produk
                    WHERE c.id_user = user_id;

                DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

                SET total = 0;
                OPEN cart_cursor;

                read_loop: LOOP
                    FETCH cart_cursor INTO item_harga, item_jumlah;
                    IF done THEN
                        LEAVE read_loop;
                    END IF;

                    SET total = total + (item_harga * item_jumlah);
                END LOOP;

                CLOSE cart_cursor;

                RETURN total;
            END ;

        ');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('function_hitung_total_harga');
    }
}
