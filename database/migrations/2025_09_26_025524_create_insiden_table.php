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
            $table->string('puskesmas_id', 13);
            $table->integer('tahapan_id')->unsigned();
            $table->integer('status_id')->unsigned();
            $table->integer('reported_by')->unsigned()->nullable();
            $table->date('reported_date')->nullable();
            $table->string('insiden')->nullable();
            $table->date('waktu_kejadian')->nullable();
            $table->text('detail_kejadian')->nullable();
            $table->string('nama_korban')->nullable();
            $table->text('tindak_lanjut')->nullable();
            $table->string('dokumentasi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('insiden', function (Blueprint $table) {
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
            $table->foreign('tahapan_id')->references('id')->on('tahapan');
            $table->foreign('reported_by')->references('id')->on('users');
            $table->foreign('status_id')->references('id')->on('status_insiden');
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
