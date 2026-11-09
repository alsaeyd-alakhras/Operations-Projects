<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * يمنح كل مستخدمي الفروع الحاليين صلاحيتي عرض وتعديل حالات المركز الكويتي
 * (تحديد رأي مدير المكتب ونسبة الاحتياج ورأي الباحث). آمن عند إعادة التشغيل: لا يكرر الصف الموجود.
 * مستخدمو الفروع الجدد يأخذونها من نموذج المستخدم (خانات الصلاحيات).
 */
return new class extends Migration
{
    private const ABILITIES = ['kuwaiticases.view', 'kuwaiticases.update'];

    public function up(): void
    {
        $userIds = DB::table('users')->whereNotNull('branch_id')->pluck('id');

        foreach ($userIds as $userId) {
            foreach (self::ABILITIES as $ability) {
                $exists = DB::table('role_user')
                    ->where('user_id', $userId)
                    ->where('role_name', $ability)
                    ->exists();

                if (! $exists) {
                    DB::table('role_user')->insert([
                        'user_id' => $userId,
                        'role_name' => $ability,
                        'ability' => 'allow',
                    ]);
                }
            }
        }
    }

    public function down(): void
    {
        // لا نحذف شيئًا: لا يمكن التمييز بين ما أضافته هذه الهجرة وما أُضيف يدويًا بعدها.
    }
};
