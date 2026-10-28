<?php

namespace App\Observers;

use App\Models\Logs;
use App\Models\Record;
use Illuminate\Support\Facades\Auth;

class RecordObserver
{
    /**
     * Handle the Record "created" event.
     */
    public function created(Record $record): void
    {
        Logs::create([
            'type' => 'create',
            'message' => 'تم أضافة مريض جديد : ' . $record->name,
            'user_id' => Auth::user()->id,
            'user_name' => Auth::user()->name,
        ]);
    }

    /**
     * Handle the Record "updated" event.
     */
    public function updated(Record $record): void
    {
        $changes = array_values(array_diff(array_keys($record->getChanges()), ['updated_at']));
        $isBranchUpdate = !empty($changes) && empty(array_diff($changes, ['eligible', 'branch_notes']));
        $isManagementUpdate = !empty($changes) && empty(array_diff($changes, ['management_status', 'management_notes']));
        Logs::create([
            'type' => 'update',
            'message' => $isBranchUpdate ? 'تم تحديث استحقاق/ملاحظات الفرع لمريض' : ($isManagementUpdate ? 'تم تحديث موافقة الإدارة لمريض : ' . $record->name : 'تم  تعديل بيانات مريض : ' . $record->name),
            'user_id' => Auth::user()->id,
            'user_name' => Auth::user()->name,
        ]);
    }

    /**
     * Handle the Record "deleted" event.
     */
    public function deleted(Record $record): void
    {
        //
    }

    /**
     * Handle the Record "restored" event.
     */
    public function restored(Record $record): void
    {
        //
    }

    /**
     * Handle the Record "force deleted" event.
     */
    public function forceDeleted(Record $record): void
    {
        //
    }
}
