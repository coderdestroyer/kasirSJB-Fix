<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
{
    Schema::create('logs', function (Blueprint $table) {
        $table->id();
        $table->string('log_target');
        $table->text('log_description');
        $table->timestamp('log_time')->useCurrent();
        $table->timestamps();
    });
}
}
