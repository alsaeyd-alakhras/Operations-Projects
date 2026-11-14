<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * حالات الكويتي:
 *  - حقل جديد «تمت» (done).
 *  - دمج «رأي الباحث الاجتماعي» (researcher_opinion) في «ملاحظات الفرع» (branch_notes):
 *    النص القديم ينتقل إلى ملاحظات الفرع، وإن كان فيها نص أصلًا يوضع النصان تحت بعض (ملاحظات الفرع أولًا ثم رأي الباحث)،
 *    ثم يُحذف عمود researcher_opinion.
 * آمنة عند إعادة التشغيل أو على سيرفر أعمدته مختلفة قليلًا (كل خطوة محروسة بـ hasColumn).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('kuwaiti_cases')) {
            return;
        }

        if (! Schema::hasColumn('kuwaiti_cases', 'branch_notes')) {
            Schema::table('kuwaiti_cases', function (Blueprint $table) {
                $table->text('branch_notes')->nullable();
            });
        }

        if (! Schema::hasColumn('kuwaiti_cases', 'done')) {
            Schema::table('kuwaiti_cases', function (Blueprint $table) {
                $table->boolean('done')->default(false);
            });
        }

        if (Schema::hasColumn('kuwaiti_cases', 'researcher_opinion')) {
            // CONCAT_WS يتجاوز القيم الفارغة (NULL)، وNULLIF يحوّل النص الفارغ إلى NULL، فلا يظهر سطر فارغ إضافي
            DB::statement(
                "UPDATE kuwaiti_cases
                 SET branch_notes = NULLIF(CONCAT_WS(CHAR(10), NULLIF(branch_notes, ''), NULLIF(researcher_opinion, '')), '')
                 WHERE researcher_opinion IS NOT NULL AND researcher_opinion <> ''"
            );

            Schema::table('kuwaiti_cases', function (Blueprint $table) {
                $table->dropColumn('researcher_opinion');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('kuwaiti_cases')) {
            return;
        }

        // ملاحظة: النص الذي دُمج في ملاحظات الفرع لا يمكن فصله من جديد، فيعود العمود فارغًا.
        if (! Schema::hasColumn('kuwaiti_cases', 'researcher_opinion')) {
            Schema::table('kuwaiti_cases', function (Blueprint $table) {
                $table->text('researcher_opinion')->nullable();
            });
        }

        foreach (['done', 'branch_notes'] as $column) {
            if (Schema::hasColumn('kuwaiti_cases', $column)) {
                Schema::table('kuwaiti_cases', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
