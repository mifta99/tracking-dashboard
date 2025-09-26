<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKeluhanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('keluhan', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->integer('equipment_id')->unsigned();
            $table->integer('kategori_id')->unsigned();
            $table->integer('status_id')->unsigned();
            $table->integer('reported_by')->unsigned()->nullable();
            $table->date('reported_date')->nullable();
            $table->text('reported_issue')->nullable();
            $table->integer('proceed_by')->unsigned()->nullable();
            $table->date('proceed_date')->nullable();
            $table->integer('resolved_by')->unsigned()->nullable();
            $table->date('resolved_date')->nullable();
            $table->text('action_taken')->nullable();
            $table->text('catatan')->nullable();
            $table->integer('total_downtime')->nullable()->unsigned();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('keluhan', function (Blueprint $table) {
            $table->foreign('equipment_id')->references('id')->on('equipment');
            $table->foreign('kategori_id')->references('id')->on('kategori_keluhan');
            $table->foreign('status_id')->references('id')->on('status_keluhan');
            $table->foreign('reported_by')->references('id')->on('users');
            $table->foreign('proceed_by')->references('id')->on('users');
            $table->foreign('resolved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('keluhan');
    }
}
