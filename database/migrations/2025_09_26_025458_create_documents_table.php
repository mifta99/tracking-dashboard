<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->string('puskesmas_id', 13);
            $table->string('basto')->nullable();
            $table->boolean('is_verified_basto')->default(false);
            $table->timestamp('verified_at_basto')->nullable();
            $table->string('kalibrasi')->nullable();
            $table->boolean('is_verified_kalibrasi')->default(false);
            $table->timestamp('verified_at_kalibrasi')->nullable();
            $table->string('bast')->nullable();
            $table->boolean('is_verified_bast')->default(false);
            $table->timestamp('verified_at_bast')->nullable();
            $table->string('aspak')->nullable();
            $table->boolean('is_verified_aspak')->default(false);
            $table->timestamp('verified_at_aspak')->nullable();
            $table->string('update_aspak')->nullable();
            $table->boolean('verif_kemenkes')->default(false);
            $table->timestamp('tgl_verif_kemenkes')->nullable();
            $table->boolean('verif_kemenkes_update_aspak')->default(false);
            $table->timestamp('tgl_verif_kemenkes_update_aspak')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('documents', function (Blueprint $table) {
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
        Schema::dropIfExists('documents');
    }
}
