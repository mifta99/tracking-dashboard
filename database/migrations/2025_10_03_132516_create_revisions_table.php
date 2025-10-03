<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('revisions', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            $table->string('puskesmas_id', 13);
            $table->integer('jenis_dokumen_id')->unsigned();
            $table->text('catatan')->nullable();
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('resolved_at')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->timestamp('verified_at')->nullable();
            $table->integer('revised_by')->unsigned()->nullable();
            $table->integer('resolved_by')->unsigned()->nullable();
            $table->timestamps();
        });

        Schema::table('revisions', function (Blueprint $table) {
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
            $table->foreign('jenis_dokumen_id')->references('id')->on('jenis_dokumens');
            $table->foreign('revised_by')->references('id')->on('users');
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
        Schema::dropIfExists('revisions');
    }
}
