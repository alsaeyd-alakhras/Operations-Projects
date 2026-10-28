<?php

use App\Models\ColumnProfile;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('column_profiles')) {
            Schema::create('column_profiles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->json('columns');
                $table->boolean('restricted')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasColumn('users', 'column_profile_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->foreignId('column_profile_id')->nullable()->constrained('column_profiles')->nullOnDelete();
            });
        }

        ColumnProfile::seedDefaults();
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'column_profile_id')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropConstrainedForeignId('column_profile_id');
            });
        }
        Schema::dropIfExists('column_profiles');
    }
};
