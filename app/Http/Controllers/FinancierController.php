<?php

namespace App\Http\Controllers;

use App\Models\Financier;
use App\Models\Logs;
use App\Models\Record;
use Illuminate\Http\Request;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;


class FinancierController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $request = request();
        if($request->archive == 1){
            $financiers = Financier::where('archive',1)->orderByRaw("LPAD(financier_number, 10, '0') ASC")->get();
            $archiveEnable = true;
        }else{
            $financiers = Financier::where('archive',0)->orderByRaw("LPAD(financier_number, 10, '0') ASC")->get();
            $archiveEnable = false;
        }
        return view('dashboard.financiers.index', compact('financiers','archiveEnable'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $financier = new Financier();
        $financier_numbers = Record::select('financier_number')->distinct()->pluck('financier_number')->toArray();
        return view('dashboard.financiers.create', compact('financier', 'financier_numbers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        $request->validate([
            'financier_number' => 'required|integer|unique:financiers,financier_number',
            'name'  => 'required|string|max:255',
            'stage'  => 'required|string|max:255',
            'maneger_name'  => 'nullable|string|max:255',
            'amount_ils'  => 'required|numeric',
            'number_cases'  => 'required|integer',
            'completion_project'  => 'nullable|in:on',
            'push_project'  => 'nullable|in:on',
            'project_distribution'  => 'nullable|in:on',
            'project_archive'  => 'nullable|in:on',
        ]);
        if($request->has('completion_project')) {
            $request->merge(['completion_project' => 1]);
        } else {
            $request->merge(['completion_project' => 0]);
        }
        if($request->has('push_project')) {
            $request->merge(['push_project' => 1]);
        } else {
            $request->merge(['push_project' => 0]);
        }
        if($request->has('project_distribution')) {
            $request->merge(['project_distribution' => 1]);
        }else{
            $request->merge(['project_distribution' => 0]);
        }
        if($request->has('project_archive')) {
            $request->merge(['project_archive' => 1]);
        }else{
            $request->merge(['project_archive' => 0]);
        }
        
        if($request->has('archive')){
            $request->merge(['archive' => $request->archive]);
        }else{
            $request->merge(['archive' => 0]);
        }

        $financier = Financier::create($request->all());
        Logs::create([
            'type' => 'create',
            'message' => 'تم اضافة ممول جديد [ ' . $financier->name . ' ]',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
        return redirect()->route('financiers.index')->with('success', 'تم اضافة ممول جديد بنجاح');
    }

    /**
     * Display the specified resource.
     */
    public function show(Financier $financier)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Financier $financier)
    {
        $financier_numbers = Record::select('financier_number')->distinct()->pluck('financier_number')->toArray();
        return view('dashboard.financiers.edit', compact('financier', 'financier_numbers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Financier $financier)
    {

        $request->validate([
            'financier_number' => 'required|integer|unique:financiers,financier_number,'.$financier->id,
            'name'  => 'required|string|max:255',
            'stage'  => 'required|string|max:255',
            'maneger_name'  => 'nullable|string|max:255',
            'amount_ils'  => 'required|numeric',
            'number_cases'  => 'required|integer',
            'completion_project'  => 'nullable|in:on',
            'push_project'  => 'nullable|in:on',
            'project_distribution'  => 'nullable|in:on',
            'project_archive'  => 'nullable|in:on',
        ]);
        if($request->has('completion_project')) {
            $request->merge(['completion_project' => 1]);
        } else {
            $request->merge(['completion_project' => 0]);
        }
        if($request->has('push_project')) {
            $request->merge(['push_project' => 1]);
        } else {
            $request->merge(['push_project' => 0]);
        }
        if($request->has('project_distribution')) {
            $request->merge(['project_distribution' => 1]);
        }else{
            $request->merge(['project_distribution' => 0]);
        }
        if($request->has('project_archive')) {
            $request->merge(['project_archive' => 1]);
        }else{
            $request->merge(['project_archive' => 0]);
        }

        if($request->has('archive')){
            $request->merge(['archive' => $request->archive]);
        }else{
            $request->merge(['archive' => 0]);
        }
        $financier->update($request->all());
        Logs::create([
            'type' => 'update',
            'message' => 'تم تعديل الممول [ ' . $financier->name . ' ]',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);

        return redirect()->route('financiers.index')->with('success', 'تم تعديل بيانات الممول بنجاح');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Financier $financier)
    {
        $financier->delete();
        Logs::create([
            'type' => 'delete',
            'message' => 'تم حذف الممول [ ' . $financier->name . ' ]',
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
        return redirect()->route('financiers.index')->with('success', 'تم حذف الممول بنجاح');
    }
    public function print(Request $request)
    {
        $type = $request->type;
        Logs::create([
            'type' => 'print',
            'message' => 'تم طباعة تقرير ممولين : ' . $request->report,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
        if($type == 'all'){
            $financiers  = Financier::all();
            $pdf = PDF::loadView('dashboard.reports.financiers',['financiers' =>  $financiers],[],[
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font_size' => 14,
                'default_font' => 'Arial',
            ]);
            return $pdf->stream();
        }

        if($type == 'single'){
            $financier  = Financier::findOrFail($request->id);
            $records = Record::where('financier_number', $financier->financier_number)->get();
            $pdf = PDF::loadView('dashboard.reports.financier',['financier' =>  $financier,'records' =>  $records],[],[
                'mode' => 'utf-8',
                'format' => 'A4',
                'default_font_size' => 14,
                'default_font' => 'Arial',
            ]);
            return $pdf->stream();
        }

    }

    public function archive(Request $request)
    {
        $financier = Financier::findOrFail($request->id);

        $financier->update([
            'archive' => $financier->archive == 1 ? 0 : 1,
        ]);

        return redirect()->route('financiers.index')->with('success', 'تم أرشفة الممول بنجاح'); 
    }
}
