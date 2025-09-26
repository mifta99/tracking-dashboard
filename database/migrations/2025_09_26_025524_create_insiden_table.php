<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInsidenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('insiden', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->string('puskesmas_id', 9);
            $table->integer('tahapan_id')->unsigned();
            $table->integer('reported_by')->unsigned()->nullable();
            $table->date('reported_date')->nullable();
            $table->text('detail')->nullable();
            $table->string('dokumentasi')->nullable();
            $table->text('action_taken')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('insiden', function (Blueprint $table) {
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
            $table->foreign('tahapan_id')->references('id')->on('tahapan');
            $table->foreign('reported_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('insiden');
    }
}
