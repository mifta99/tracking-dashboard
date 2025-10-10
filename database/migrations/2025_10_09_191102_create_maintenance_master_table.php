<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance_master', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->integer('kuartal');
            $table->date('start_period');
            $table->date('end_period');
            $table->text('description');
            $table->text('layanan');
            $table->string('waktu_pengecekan');
            $table->string('kunjungan', 1);
            $table->integer('total_active_days');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('maintenance_master');
    }
}
