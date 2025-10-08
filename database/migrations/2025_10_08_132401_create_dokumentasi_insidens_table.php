<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDokumentasiInsidensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dokumentasi_insidens', function (Blueprint $table) {
            $table->id();
            $table->integer('insiden_id')->unsigned();
            $table->string('link_foto')->nullable(false);
            $table->foreign('insiden_id')->references('id')->on('insiden');
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
        Schema::dropIfExists('dokumentasi_insidens');
    }
}
