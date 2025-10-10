<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->string('puskesmas_id', 13);
            $table->string('serial_number');
            $table->string('name')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('equipment', function (Blueprint $table) {
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment');
    }
}
