<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * العمودان notes_2 و address أُضيفا يدويًا عبر phpMyAdmin بدون migration،
     * فنضيف كلًا منهما هنا فقط إذا كان غير موجود.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('records', 'notes_2')) {
            Schema::table('records', function (Blueprint $table) {
                $table->text('notes_2')->nullable()->after('notes');
            });
        }

        if (! Schema::hasColumn('records', 'address')) {
            Schema::table('records', function (Blueprint $table) {
                $table->string('address')->nullable()->after('notes_2');
            });
        }
    }

    public function down(): void
    {
        // الأعمدة قد تكون موجودة من قبل خارج الـ migrations، فلا نحذفها.
    }
};
