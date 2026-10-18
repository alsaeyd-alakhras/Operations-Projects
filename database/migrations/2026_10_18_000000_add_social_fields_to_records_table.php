<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->string('displacement_status')->nullable()->after('archived');
            $table->string('income_status')->nullable()->after('displacement_status');
            $table->string('family_burden')->nullable()->after('income_status');
            $table->boolean('distributed')->nullable()->after('family_burden');
        });
    }

    public function down(): void
    {
        Schema::table('records', function (Blueprint $table) {
            $table->dropColumn(['displacement_status', 'income_status', 'family_burden', 'distributed']);
        });
    }
};
