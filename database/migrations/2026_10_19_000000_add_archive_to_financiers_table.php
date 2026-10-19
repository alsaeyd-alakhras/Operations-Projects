<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * العمود archive يستخدمه FinancierController و Financier::$fillable
     * لكنه لم يُنشأ في أي migration (أُضيف يدويًا في بعض البيئات)،
     * فنضيفه هنا فقط إذا كان غير موجود.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('financiers', 'archive')) {
            Schema::table('financiers', function (Blueprint $table) {
                $table->boolean('archive')->default(0)->after('project_archive');
            });
        }
    }

    public function down(): void
    {
        // العمود قد يكون موجودًا من قبل خارج الـ migrations، فلا نحذفه.
    }
};
