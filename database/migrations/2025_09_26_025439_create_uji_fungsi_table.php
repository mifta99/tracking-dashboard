<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUjiFungsiTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('uji_fungsi', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->string('puskesmas_id', 9);
            $table->date('target_tgl_uji_fungsi')->nullable();
            $table->date('tgl_instalasi')->nullable();
            $table->string('doc_instalasi')->nullable();
            $table->date('tgl_pelatihan')->nullable();
            $table->string('doc_pelatihan')->nullable();
            $table->date('tgl_uji_fungsi')->nullable();
            $table->string('doc_uji_fungsi')->nullable();
            $table->text('catatan')->nullable();
            $table->boolean('verif_kemenkes')->default(false);
            $table->timestamp('tgl_verif_kemenkes')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('uji_fungsi', function (Blueprint $table) {
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
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
        Schema::dropIfExists('uji_fungsi');
    }
}
