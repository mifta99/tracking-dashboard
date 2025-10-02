<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->integer('id')->autoIncrement()->unsigned();
            // $table->foreignId('role_id')->constrained('roles')->onDelete('cascade');
            $table->integer('role_id')->unsigned();
            $table->string('puskesmas_id', 13)->nullable();
            $table->string('name');
            $table->string('jabatan')->nullable();
            $table->string('instansi')->nullable();
            $table->string('no_hp')->nullable();
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreign('role_id')->references('id')->on('roles');
            $table->foreign('puskesmas_id')->references('id')->on('puskesmas');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
