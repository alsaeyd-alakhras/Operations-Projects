<?php

namespace App\Http\Controllers;

use App\Models\ColumnProfile;
use App\Models\Logs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ColumnProfileController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('view', ColumnProfile::class);
        $profiles = ColumnProfile::withCount('users')->orderBy('id')->get();
        return view('dashboard.column-profiles.index', compact('profiles'));
    }

    public function create()
    {
        $this->authorize('create', ColumnProfile::class);
        $profile = new ColumnProfile(['columns' => [], 'restricted' => false]);
        return view('dashboard.column-profiles.create', compact('profile'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', ColumnProfile::class);
        $profile = ColumnProfile::create($this->validated($request));
        $this->log($request, 'create', 'تم اضافة ملف أعمدة جديد [ ' . $profile->name . ' ]');
        return redirect()->route('column-profiles.index')->with('success', 'تم اضافة ملف الأعمدة بنجاح');
    }

    public function show(ColumnProfile $columnProfile)
    {
        return redirect()->route('column-profiles.index');
    }

    public function edit(ColumnProfile $columnProfile)
    {
        $this->authorize('update', ColumnProfile::class);
        $profile = $columnProfile;
        $btn_label = 'تعديل';
        return view('dashboard.column-profiles.edit', compact('profile', 'btn_label'));
    }

    public function update(Request $request, ColumnProfile $columnProfile)
    {
        $this->authorize('update', ColumnProfile::class);
        $columnProfile->update($this->validated($request, $columnProfile));
        $this->log($request, 'update', 'تم تعديل ملف الأعمدة [ ' . $columnProfile->name . ' ]');
        return redirect()->route('column-profiles.index')->with('success', 'تم تعديل ملف الأعمدة بنجاح');
    }

    public function destroy(Request $request, ColumnProfile $columnProfile)
    {
        $this->authorize('delete', ColumnProfile::class);
        $name = $columnProfile->name;
        // المستخدمون المرتبطون يعودون للسلوك الافتراضي (بدون ملف)
        $columnProfile->users()->update(['column_profile_id' => null]);
        $columnProfile->delete();
        $this->log($request, 'delete', 'تم حذف ملف الأعمدة [ ' . $name . ' ]');
        return redirect()->route('column-profiles.index')->with('success', 'تم حذف ملف الأعمدة بنجاح');
    }

    private function validated(Request $request, ?ColumnProfile $profile = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('column_profiles', 'name')->ignore($profile?->id)],
            'columns' => ['nullable', 'array'],
            'columns.*' => ['string', Rule::in(array_keys(ColumnProfile::COLUMNS))],
            'restricted' => ['nullable', 'boolean'],
        ]);

        return [
            'name' => $data['name'],
            'columns' => array_values(array_intersect(array_keys(ColumnProfile::COLUMNS), $data['columns'] ?? [])),
            'restricted' => $request->boolean('restricted'),
        ];
    }

    private function log(Request $request, string $type, string $message): void
    {
        Logs::create([
            'type' => $type,
            'message' => $message,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
    }
}
