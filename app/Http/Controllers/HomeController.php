<?php

namespace App\Http\Controllers;

use App\Models\KuwaitiCase;
use App\Models\Record;
use Illuminate\Http\Request;
use Throwable;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $canYafa = $user->can('view', Record::class);
        $canKuwaiti = $user->can('view', KuwaitiCase::class);

        // قسم واحد فقط: مباشرة إليه
        if ($canYafa && ! $canKuwaiti) {
            return redirect()->route('records.index');
        }
        if ($canKuwaiti && ! $canYafa) {
            return redirect()->route('kuwaiti-cases.index');
        }

        $yafaCount = null;
        $kuwaitiChip = null;

        if ($canYafa) {
            try {
                $yafaCount = Record::where('archived', 0)->count();
            } catch (Throwable $e) {
                $yafaCount = null;
            }
        }

        if ($canKuwaiti) {
            try {
                $base = KuwaitiCase::where('archived', 0);
                if ($user->isBranchUser()) {
                    $kuwaitiChip = ['label' => 'بانتظار رأي مدير المكتب', 'count' => (clone $base)->whereNull('eligible')->count()];
                } elseif ($user->can('management', KuwaitiCase::class)) {
                    $kuwaitiChip = ['label' => 'بانتظار موافقة الإدارة', 'count' => (clone $base)->where(function ($q) {
                        $q->whereNull('management_status')->orWhere('management_status', '')->orWhere('management_status', 'pending');
                    })->count()];
                } else {
                    $kuwaitiChip = ['label' => 'إجمالي الحالات', 'count' => (clone $base)->count()];
                }
            } catch (Throwable $e) {
                $kuwaitiChip = null;
            }
        }

        return view('dashboard.home', compact('canYafa', 'canKuwaiti', 'yafaCount', 'kuwaitiChip'));
    }
}
