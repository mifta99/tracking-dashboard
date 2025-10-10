<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('kategori_keluhan', function (Blueprint $table) {
            if (!Schema::hasColumn('kategori_keluhan','description')) {
                $table->text('description')->nullable()->after('kategori');
            }
            if (!Schema::hasColumn('kategori_keluhan','max_response_time')) {
                $table->integer('max_response_time')->nullable()->after('description');
            }
            if (!Schema::hasColumn('kategori_keluhan','max_technical_time')) {
                $table->integer('max_technical_time')->nullable()->after('max_response_time');
            }
            if (!Schema::hasColumn('kategori_keluhan','max_resolution_time')) {
                $table->integer('max_resolution_time')->nullable()->after('max_technical_time');
            }
        });

    }

    public function down(): void
    {
        Schema::table('kategori_keluhan', function (Blueprint $table) {
            if (Schema::hasColumn('kategori_keluhan','description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('kategori_keluhan','max_response_time')) {
                $table->dropColumn('max_response_time');
            }
            if (Schema::hasColumn('kategori_keluhan','max_technical_time')) {
                $table->dropColumn('max_technical_time');
            }
            if (Schema::hasColumn('kategori_keluhan','max_resolution_time')) {
                $table->dropColumn('max_resolution_time');
            }
        });
    }
};
