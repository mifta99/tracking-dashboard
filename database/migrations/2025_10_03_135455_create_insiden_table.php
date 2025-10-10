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
            $table->integer('equipment_id')->nullable()->unsigned();
            $table->integer('tahapan_id')->unsigned();
            $table->integer('status_id')->unsigned();
            $table->integer('kategori_id')->unsigned();
            $table->date('tgl_kejadian')->nullable();
            $table->string('nama_korban')->nullable();
            $table->string('bagian')->nullable();
            $table->string('insiden')->nullable();
            $table->text('kronologis')->nullable();
            $table->text('tindakan')->nullable();
            $table->date('tgl_selesai')->nullable();
            $table->string('doc_selesai')->nullable();
            $table->string('rencana_tindakan_koreksi')->nullable();
            $table->string('pelaksana_tindakan_koreksi')->nullable();
            $table->date('tgl_selesai_koreksi')->nullable();
            $table->string('verifikasi_hasil_koreksi')->nullable();
            $table->date('verifikasi_tgl_koreksi')->nullable();
            $table->string('verifikasi_pelaksana_koreksi')->nullable();
            $table->string('rencana_tindakan_korektif')->nullable();
            $table->string('pelaksana_tindakan_korektif')->nullable();
            $table->date('tgl_selesai_korektif')->nullable();
            $table->string('verifikasi_hasil_korektif')->nullable();
            $table->date('verifikasi_tgl_korektif')->nullable();
            $table->string('verifikasi_pelaksana_korektif')->nullable();
            $table->integer('reported_by')->unsigned()->nullable();
            $table->string('dokumentasi')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('insiden', function (Blueprint $table) {
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
            $table->foreign('equipment_id')->references('id')->on('equipment');
            $table->foreign('tahapan_id')->references('id')->on('tahapan');
            $table->foreign('status_id')->references('id')->on('status_insiden');
            $table->foreign('kategori_id')->references('id')->on('kategori_insidens');
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
