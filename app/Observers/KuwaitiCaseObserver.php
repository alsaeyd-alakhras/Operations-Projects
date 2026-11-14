<?php

namespace App\Observers;

use App\Models\KuwaitiCase;
use App\Models\Logs;
use Illuminate\Support\Facades\Auth;

class KuwaitiCaseObserver
{
    public function created(KuwaitiCase $case): void
    {
        $this->log('create', 'حالة الكويتي: تم إضافة حالة جديدة : ' . $case->name);
    }

    public function updated(KuwaitiCase $case): void
    {
        $changes = array_values(array_diff(array_keys($case->getChanges()), ['updated_at', 'total_score']));
        $isBranchUpdate = ! empty($changes) && empty(array_diff($changes, ['eligible', 'need_percentage', 'branch_notes']));
        $isManagementUpdate = ! empty($changes) && empty(array_diff($changes, ['management_status', 'management_notes']));
        $isDoneUpdate = ! empty($changes) && empty(array_diff($changes, ['done', 'archived']));

        $this->log('update', $isBranchUpdate
            ? 'حالة الكويتي: تم تحديث رأي الفرع لحالة : ' . $case->name
            : ($isManagementUpdate
                ? 'حالة الكويتي: تم تحديث موافقة الإدارة لحالة : ' . $case->name
                : ($isDoneUpdate
                    ? 'تم تحديث حالة تمت لحالة الكويتي : ' . $case->name . ($case->archived ? ' (نُقلت إلى الأرشيف)' : '')
                    : 'حالة الكويتي: تم تعديل بيانات حالة : ' . $case->name)));
    }

    private function log(string $type, string $message): void
    {
        $user = Auth::user();
        Logs::create([
            'type' => $type,
            'message' => $message,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
        ]);
    }
}
