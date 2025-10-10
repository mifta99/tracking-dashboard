<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaintenanceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('maintenance', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->integer('maintenance_master_id')->unsigned();
            $table->integer('equipment_id')->unsigned();
            $table->string('puskesmas_id', 13);
            $table->date('tgl_maintenance');
            $table->string('dokumentasi')->nullable();
            $table->string('berita_acara')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
        });

        Schema::table('maintenance', function (Blueprint $table) {
            $table->foreign('maintenance_master_id')->references('id')->on('maintenance_master');
            $table->foreign('equipment_id')->references('id')->on('equipment');
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
        Schema::dropIfExists('maintenances');
    }
}
