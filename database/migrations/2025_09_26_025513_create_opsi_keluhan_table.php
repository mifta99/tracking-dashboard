<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpsiKeluhanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opsi_keluhan', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->integer('kategori_keluhan_id')->unsigned();
            $table->string('opsi');
        });

        Schema::table('opsi_keluhan', function (Blueprint $table) {
            $table->foreign('kategori_keluhan_id')->references('id')->on('kategori_keluhan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('opsi_keluhan');
    }
}
