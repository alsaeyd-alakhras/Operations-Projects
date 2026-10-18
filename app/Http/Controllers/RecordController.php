<?php

namespace App\Http\Controllers;

use App\Exports\RecordsExport;
use App\Imports\RecordsImport;
use App\Models\Financier;
use App\Models\Logs;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf as PDF;
use Yajra\DataTables\Facades\DataTables;

class RecordController extends Controller
{
    use AuthorizesRequests;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('view', Record::class);
        if($request->ajax()) {
            // جلب بيانات المستخدمين من الجدول
            $records = Record::query()->where('archived', 0)->orderBy('date', 'asc');;
            // التصفية بناءً على التواريخ
            if ($request->from_date != null && $request->to_date != null) {
                $records->whereBetween('date', [$request->from_date, $request->to_date]);
            }

            if ($request->from_age != null && $request->to_age != null) {
                $records->whereBetween('age', [$request->from_age, $request->to_age]);
            }

            if($request->fieldNull != null) {
                $records->whereNull($request->fieldNull);
            }

            return DataTables::of($records)
                    ->addIndexColumn()  // إضافة عمود الترقيم التلقائي
                    ->addColumn('financier', function ($record) {
                        $financier = Financier::where('financier_number', $record->financier_number)->first();
                        if($financier != null) {
                            return $financier->name;
                        }
                    })
                    ->addColumn('edit', function ($record) {
                        return $record->id;
                    })
                    ->addColumn('archived', function ($record) {
                        return $record->id;
                    })
                    ->addColumn('delete', function ($record) {
                        return $record->id;
                    })
                    ->make(true);
        }

        $names = Record::select('name')->distinct()->pluck('name')->toArray();
        $pat_ids = Record::select('patient_ID')->distinct()->pluck('patient_ID')->toArray();
        $financiers = Record::select('financier_number')->distinct()->pluck('financier_number')->toArray();
        $operations = Record::select('operation')->distinct()->pluck('operation')->toArray();
        $doctors = Record::select('doctor')->distinct()->pluck('doctor')->toArray();
        $anesthesias = Record::select('anesthesia')->distinct()->pluck('anesthesia')->toArray();
        $user_names = Record::select('user_name')->distinct()->pluck('user_name')->toArray();
        return view('dashboard.records.index', compact('names','pat_ids', 'financiers', 'operations', 'doctors', 'anesthesias', 'user_names'));
    }

    public function archivedRow(Request $request){
        $this->authorize('view', Record::class);
        if($request->ajax()) {
            $record = Record::find($request->id);
            $type = $request->type;
            if($type == 'restore') {
                $record->archived = 0;
            }
            if($type == 'archived') {
                $record->archived = 1;
            }
            $record->save();
            return response()->json($record);
        }
    }
    public function archived(Request $request)
    {
        $this->authorize('view', Record::class);
        if($request->ajax()) {
            // جلب بيانات المستخدمين من الجدول
            $records = Record::query()->where('archived', 1)->orderBy('date', 'asc');;
            // التصفية بناءً على التواريخ
            if ($request->from_date != null && $request->to_date != null) {
                $records->whereBetween('date', [$request->from_date, $request->to_date]);
            }

            if ($request->from_age != null && $request->to_age != null) {
                $records->whereBetween('age', [$request->from_age, $request->to_age]);
            }

            if($request->fieldNull != null) {
                $records->whereNull($request->fieldNull);
            }

            return DataTables::of($records)
                    ->addIndexColumn()  // إضافة عمود الترقيم التلقائي
                    ->addColumn('financier', function ($record) {
                        $financier = Financier::where('financier_number', $record->financier_number)->first();
                        if($financier != null) {
                            return $financier->name;
                        }
                    })
                    ->addColumn('edit', function ($record) {
                        return $record->id;
                    })
                    ->addColumn('archived', function ($record) {
                        return $record->id;
                    })
                    ->addColumn('delete', function ($record) {
                        return $record->id;
                    })
                    ->make(true);
        }

        $names = Record::select('name')->distinct()->pluck('name')->toArray();
        $pat_ids = Record::select('patient_ID')->distinct()->pluck('patient_ID')->toArray();
        $financiers = Record::select('financier_number')->distinct()->pluck('financier_number')->toArray();
        $operations = Record::select('operation')->distinct()->pluck('operation')->toArray();
        $doctors = Record::select('doctor')->distinct()->pluck('doctor')->toArray();
        $anesthesias = Record::select('anesthesia')->distinct()->pluck('anesthesia')->toArray();
        $user_names = Record::select('user_name')->distinct()->pluck('user_name')->toArray();
        return view('dashboard.records.archived', compact('names','pat_ids', 'financiers', 'operations', 'doctors', 'anesthesias', 'user_names'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', Record::class);
        $record = new Record();
        if($request->ajax()) {
            $record->date = Carbon::now()->format('Y-m-d');
            $record->user = $record->user;
            return response()->json($record);
        }
        // return view('dashboard.projects.allocations.create', compact('allocation'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', Record::class);
        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'patient_ID' => 'required|integer',
            'age' => 'numeric|min:0|max:150',
            'phone_number1' => 'required',
            'operation' => 'required',
            'doctor' => 'required',
            'amount' => 'required',
        ]);

        $request->merge([
            'done' => $request->done ? 1 : 0,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name
        ]);
        Record::create($request->all());
        if($request->ajax()) {
            return response()->json(['message' => 'تم الإضافة بنجاح']);
        }
        return redirect()->back()->with('success', 'تم إضافة مريض جديد بنجاح');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Record $record)
    {
        $this->authorize('update', Record::class);
        if($request->ajax()) {
            $record->user = $record->user;
            return response()->json($record);
        }
        $financier_numbers = Record::select('financier_number')->distinct()->pluck('financier_number')->toArray();
        $operations = Record::select('operation')->distinct()->pluck('operation')->toArray();
        $doctors = Record::select('doctor')->distinct()->pluck('doctor')->toArray();
        $anesthesias = Record::select('anesthesia')->distinct()->pluck('anesthesia')->toArray();
        return view('dashboard.records.edit', compact('record', 'financier_numbers', 'operations', 'doctors', 'anesthesias'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Record $record)
    {
        $this->authorize('update', Record::class);

        $request->validate([
            'name' => 'required|string',
            'date' => 'required|date',
            'patient_ID' => 'required|integer',
            'age' => 'numeric|min:0|max:150',
            'phone_number1' => 'required',
            'operation' => 'required',
            'doctor' => 'required',
            'amount' => 'required',
        ]);
        $request->merge([
            'done' => $request->done ? 1 : 0,
        ]);
        $record->update($request->all());
        if($request->ajax()) {
            return response()->json(['message' => 'تم التحديث بنجاح']);
        }
        return redirect()->route('records.index')->with('success', 'تم تعديل بيانات المريض');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id)
    {
        $this->authorize('delete', Record::class);
        $record = Record::findOrFail($id);
        $record->delete();
        if($request->ajax()) {
            return response()->json(['message' => 'تم حذف المريض بنجاح']);
        }
        return redirect()->back()->with('success', 'تم حذف المريض بنجاح');
    }


    public function import(Request $request)
    {
        $this->authorize('import', Record::class);
        $file = $request->file('file');
        if (!$file) {
            return redirect()->back()->with('error', 'Please select a file to upload.');
        }
        Excel::import(new RecordsImport, $file);

        return redirect()->route('records.index')->with('success', 'تم رفع ملف الإكسيل بنجاح');
    }
    public function export(Request $request)
    {
        $this->authorize('exportexcel', Record::class);
        $data = json_decode($request->data, true);
        // تحويل البيانات إلى collect
        $collection = collect($data);
        return Excel::download(new RecordsExport($collection), 'سجلات المرضى ' . Carbon::now()->format('Y-m-d H:i:s') . '.xlsx');
    }

    public function print(Request $request)
    {
        $this->authorize('export', Record::class);
        $records = json_decode($request->data, true); // 'true' لتحويلها إلى مصفوفة
        $recordsCollection = collect($records); // تحويل المصفوفة إلى Collection
        $financiers_numbers = $recordsCollection->pluck('financier_number')->unique()->toArray();
        $financiers = Financier::whereIn('financier_number', $financiers_numbers)->pluck('name')->toArray();
        Logs::create([
            'type' => 'print',
            'message' => 'تم طباعة تقرير : ' . $request->report,
            'user_id' => $request->user()->id,
            'user_name' => $request->user()->name,
        ]);
        // دوال الموجوع اخر سطر في التقرير
        $recordsTotal = collect($records)->map(function ($record){
            return [
                "amount" => $record['amount'] ?? '0',
                "doctor_share" => $record['doctor_share'] ?? '0',
                "anesthesiologists_share" => $record['anesthesiologists_share'] ?? '0',
                "bed" => $record['bed'] ?? '0',
                "private" => $record['private'] ?? '0',
            ];
        });
        $recordsTotalArray = [
            'amount' => collect($recordsTotal->pluck('amount')->toArray())->sum(),
            'doctor_share' => collect($recordsTotal->pluck('doctor_share')->toArray())->sum(),
            'anesthesiologists_share' => collect($recordsTotal->pluck('anesthesiologists_share')->toArray())->sum(),
            'bed' => collect($recordsTotal->pluck('bed')->toArray())->sum(),
            'private' => collect($recordsTotal->pluck('private')->toArray())->sum(),
        ];

        if($request->report == 'basic'){
            $pdf = PDF::loadView('dashboard.reports.basic',['records' =>  $recordsCollection,'recordsTotalArray' => $recordsTotalArray,'financiers' => $financiers],[],[
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'default_font_size' => 12,
                'default_font' => 'Arial',
            ]);
            return $pdf->stream();
        }
        if($request->report == 'mali'){
            $pdf = PDF::loadView('dashboard.reports.mali',['records' =>  $recordsCollection,'recordsTotalArray' => $recordsTotalArray],[],[
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'default_font_size' => 12,
                'default_font' => 'Arial',
            ]);
            return $pdf->stream();
        }
    }
}
