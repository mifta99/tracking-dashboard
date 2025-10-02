<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('puskesmas', function (Blueprint $table) {
            if (!Schema::hasColumn('puskesmas','no_hp')) {
                $table->string('no_hp',50)->nullable()->after('pic_dinkes_kab');
            }
            if (!Schema::hasColumn('puskesmas','no_hp_alternatif')) {
                $table->string('no_hp_alternatif',50)->nullable()->after('no_hp');
            }
        });
    }

    public function down(): void
    {
        Schema::table('puskesmas', function (Blueprint $table) {
            if (Schema::hasColumn('puskesmas','no_hp_alternatif')) {
                $table->dropColumn('no_hp_alternatif');
            }
            if (Schema::hasColumn('puskesmas','no_hp')) {
                $table->dropColumn('no_hp');
            }
        });
    }
};
