<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePuskesmasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('puskesmas', function (Blueprint $table) {
            $table->string('id', 13);
            $table->primary('id');
            $table->string('district_id', 7);
            $table->string('name');
            $table->string('pic')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('no_hp_alternatif')->nullable();
            $table->string('kepala')->nullable();
            $table->string('pic_dinkes_prov')->nullable();
            $table->string('pic_dinkes_kab')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('puskesmas', function (Blueprint $table) {
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('puskesmas');
    }
}
