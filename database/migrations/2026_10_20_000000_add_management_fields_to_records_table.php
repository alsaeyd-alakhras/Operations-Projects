<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            if (! Schema::hasColumn('records', 'management_status')) {
                $table->string('management_status')->nullable();
            }
            if (! Schema::hasColumn('records', 'management_notes')) {
                $table->text('management_notes')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            foreach (['management_status', 'management_notes'] as $column) {
                if (Schema::hasColumn('records', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
