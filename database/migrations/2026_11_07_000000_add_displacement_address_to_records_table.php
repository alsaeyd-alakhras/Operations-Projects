<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('records', 'displacement_address')) {
            Schema::table('records', function (Blueprint $table) {
                $table->string('displacement_address')->nullable()->after('address');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('records', 'displacement_address')) {
            Schema::table('records', function (Blueprint $table) {
                $table->dropColumn('displacement_address');
            });
        }
    }
};
