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
            $table->date('start_period')->nullable();
            $table->date('end_period')->nullable();
            $table->text('description')->nullable();
            $table->text('layanan')->nullable();
            $table->string('waktu_pengecekan')->nullable();
            $table->string('kunjungan', 1)->nullable();
            $table->integer('total_active_days')->nullable()->unsigned();
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
