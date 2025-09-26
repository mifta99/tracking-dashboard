<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePengirimanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pengiriman', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->string('puskesmas_id', 9);
            $table->date('tgl_pengiriman')->nullable();
            $table->integer('eta')->nullable();
            $table->string('resi')->nullable();
            $table->string('tracking_link')->nullable();
            $table->integer('equipment_id')->unsigned();
            $table->date('target_tgl')->nullable();
            $table->text('catatan')->nullable();
            $table->date('tgl_diterima')->nullable();
            $table->string('nama_penerima')->nullable();
            $table->string('instansi_penerima')->nullable();
            $table->string('jabatan_penerima')->nullable();
            $table->string('nomor_penerima')->nullable();
            $table->string('link_tanda_terima')->nullable();
            $table->integer('tahapan_id')->unsigned();
            $table->boolean('verif_kemenkes')->default(false);
            $table->timestamp('tgl_verif_kemenkes')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('pengiriman', function (Blueprint $table) {
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
            $table->foreign('equipment_id')->references('id')->on('equipment');
            $table->foreign('tahapan_id')->references('id')->on('tahapan');
            $table->foreign('created_by')->references('id')->on('users');
            $table->foreign('updated_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pengiriman');
    }
}
