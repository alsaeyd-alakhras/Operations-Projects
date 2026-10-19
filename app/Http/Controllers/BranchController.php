<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Logs;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('view', Branch::class);
        $branches = Branch::withCount(['records', 'users'])->orderBy('name')->get();
        return view('dashboard.branches.index', compact('branches'));
    }

    public function create()
    {
        $this->authorize('create', Branch::class);
        $branch = new Branch();
        return view('dashboard.branches.create', compact('branch'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Branch::class);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
        ]);
        $branch = Branch::create($data);
        Logs::create([
            'type' => 'create',
            'message' => 'تم اضافة فرع جديد [ ' . $branch->name . ' ]',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
        return redirect()->route('branches.index')->with('success', 'تم اضافة فرع جديد بنجاح');
    }

    public function show(Branch $branch)
    {
        return redirect()->route('branches.index');
    }

    public function edit(Branch $branch)
    {
        $this->authorize('update', Branch::class);
        $btn_label = 'تعديل';
        return view('dashboard.branches.edit', compact('branch', 'btn_label'));
    }

    public function update(Request $request, Branch $branch)
    {
        $this->authorize('update', Branch::class);
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,' . $branch->id,
        ]);
        $branch->update($data);
        Logs::create([
            'type' => 'update',
            'message' => 'تم تعديل الفرع [ ' . $branch->name . ' ]',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
        return redirect()->route('branches.index')->with('success', 'تم تعديل الفرع بنجاح');
    }

    public function destroy(Request $request, Branch $branch)
    {
        $this->authorize('delete', Branch::class);
        $name = $branch->name;
        $branch->delete();
        Logs::create([
            'type' => 'delete',
            'message' => 'تم حذف الفرع [ ' . $name . ' ]',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
        return redirect()->route('branches.index')->with('success', 'تم حذف الفرع بنجاح');
    }
}
