<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKalibrasiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
       Schema::create('kalibrasi', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->integer('kalibrasi_master_id')->unsigned();
            $table->integer('equipment_id')->unsigned();
            $table->string('puskesmas_id', 13);
            $table->date('tgl_kalibrasi');
            $table->string('dokumentasi')->nullable();
            $table->string('berita_acara')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('kalibrasi', function (Blueprint $table) {
            $table->foreign('kalibrasi_master_id')->references('id')->on('kalibrasi_master');
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
            $table->foreign('equipment_id')->references('id')->on('equipment');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kalibrasi');
    }
}
