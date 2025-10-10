<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKalibrasiMasterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kalibrasi_master', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->integer('kuartal');
            $table->date('start_period')->nullable();
            $table->date('end_period')->nullable();
            $table->text('description')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kalibrasi_master');
    }
}
