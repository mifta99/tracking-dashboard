<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKategoriKeluhanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kategori_keluhan', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->string('kategori');
            $table->text('description')->nullable();
            $table->integer('max_response_time')->nullable();
            $table->integer('max_technical_time')->nullable();
            $table->integer('max_resolution_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kategori_keluhan');
    }
}
