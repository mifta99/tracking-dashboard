<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('insiden', function (Blueprint $table) {
            if (!Schema::hasColumn('insiden','tindakan')) {
                $table->text('tindakan')->nullable()->after('kronologis');
            }
            if (!Schema::hasColumn('insiden','tgl_selesai')) {
                $table->date('tgl_selesai')->nullable()->after('tindakan');
            }
        });
    }

    public function down(): void
    {
        Schema::table('insiden', function (Blueprint $table) {
            if (Schema::hasColumn('insiden','tindakan')) {
                $table->dropColumn('tindakan');
            }
            if (Schema::hasColumn('insiden','tgl_selesai')) {
                $table->dropColumn('tgl_selesai');
            }
        });
    }
};
