<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDokumentasiKeluhansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dokumentasi_keluhans', function (Blueprint $table) {
            $table->id();
            $table->integer('keluhan_id')->unsigned();
            $table->string('link_foto')->nullable(false);
            $table->foreign('keluhan_id')->references('id')->on('keluhan');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dokumentasi_keluhans');
    }
}
