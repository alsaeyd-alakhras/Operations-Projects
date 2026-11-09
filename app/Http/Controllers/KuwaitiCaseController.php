<?php

namespace App\Http\Controllers;

use App\Exports\KuwaitiCasesExport;
use App\Imports\KuwaitiCasesImport;
use App\Models\KuwaitiCase;
use App\Models\Record;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Yajra\DataTables\Facades\DataTables;

class KuwaitiCaseController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view', KuwaitiCase::class);
        $archived = (int) $request->boolean('archived');
        if ($archived) {
            $this->authorize('archive', KuwaitiCase::class);
        }

        if ($request->ajax()) {
            $cases = $this->filteredQuery($request, $archived);

            return DataTables::of($cases)
                ->addIndexColumn()
                ->addColumn('age', fn (KuwaitiCase $case) => $case->age)
                ->addColumn('gender_label', fn (KuwaitiCase $case) => $case->gender_label)
                ->addColumn('marital_status_label', fn (KuwaitiCase $case) => $case->marital_status_label)
                ->addColumn('exam_need_label', fn (KuwaitiCase $case) => $case->exam_need_label)
                ->addColumn('housing_status_label', fn (KuwaitiCase $case) => $case->housing_status_label)
                ->addColumn('income_status_label', fn (KuwaitiCase $case) => $case->income_status_label)
                ->addColumn('family_burden_label', fn (KuwaitiCase $case) => $case->family_burden_label)
                ->addColumn('branch_name', fn (KuwaitiCase $case) => $case->branch_name_sql)
                ->addColumn('eligible_label', fn (KuwaitiCase $case) => $case->eligible_label)
                ->addColumn('need_percentage_label', fn (KuwaitiCase $case) => $case->need_percentage_label)
                ->addColumn('management_status_label', fn (KuwaitiCase $case) => $case->management_status_label)
                ->addColumn('edit', fn (KuwaitiCase $case) => $case->id)
                ->addColumn('archived', fn (KuwaitiCase $case) => $case->id)
                ->addColumn('delete', fn (KuwaitiCase $case) => $case->id)
                ->with($this->dataTableMeta($request, $archived))
                ->make(true);
        }

        $years = $this->caseYears($archived);

        return view('dashboard.kuwaiti-cases.index', compact('years', 'archived'));
    }

    public function create(Request $request)
    {
        $this->authorize('create', KuwaitiCase::class);
        $this->denyBranchUser($request);

        $case = new KuwaitiCase();
        $case->date = Carbon::now();
        $case->data_entry = $request->user()->name;

        return response()->json($this->rowData($case));
    }

    public function store(Request $request)
    {
        $this->authorize('create', KuwaitiCase::class);
        $this->denyBranchUser($request);

        $data = $request->validate($this->coreRules());
        $data['user_id'] = $request->user()->id;
        $data['data_entry'] = $data['data_entry'] ?? $request->user()->name;

        KuwaitiCase::create($data);

        return $request->ajax()
            ? response()->json(['message' => 'تم الإضافة بنجاح'])
            : redirect()->route('kuwaiti-cases.index')->with('success', 'تم إضافة الحالة بنجاح');
    }

    public function edit(Request $request, KuwaitiCase $kuwaitiCase)
    {
        $this->authorize('update', KuwaitiCase::class);

        return response()->json($this->rowData($kuwaitiCase));
    }

    public function update(Request $request, KuwaitiCase $kuwaitiCase)
    {
        $this->authorize('update', KuwaitiCase::class);

        if ($request->user()->isBranchUser()) {
            // يوزر الفرع: رأي مدير المكتب ونسبة الاحتياج ورأي الباحث فقط، وأي حقل آخر يُتجاهل
            $request->merge(['eligible' => $request->input('eligible') === '' ? null : $request->input('eligible')]);
            $data = $request->validate([
                'eligible' => ['nullable', 'boolean'],
                'need_percentage' => ['nullable', Rule::in(array_keys(KuwaitiCase::NEED_PERCENTAGES))],
                'researcher_opinion' => ['nullable', 'string', 'max:5000'],
            ]);
        } else {
            $data = $request->validate($this->coreRules());
        }

        $kuwaitiCase->update($data + $this->managementFields($request));

        return $request->ajax()
            ? response()->json(['message' => 'تم التحديث بنجاح'])
            : redirect()->route('kuwaiti-cases.index')->with('success', 'تم تعديل بيانات الحالة');
    }

    /** تحديث موافقة الإدارة وملاحظاتها (من الجدول مباشرة). */
    public function management(Request $request, KuwaitiCase $kuwaitiCase)
    {
        $this->authorize('management', KuwaitiCase::class);
        $request->merge(['management_status' => $request->input('management_status') === '' ? null : $request->input('management_status')]);
        $data = $request->validate([
            'management_status' => ['nullable', Rule::in(array_keys(Record::MANAGEMENT_STATUSES))],
            'management_notes' => ['nullable', 'string', 'max:2000'],
        ]);
        $kuwaitiCase->update([
            'management_status' => $data['management_status'] ?? null,
            'management_notes' => $data['management_notes'] ?? null,
        ]);

        return response()->json([
            'status' => $kuwaitiCase->management_status,
            'status_label' => $kuwaitiCase->management_status_label,
            'notes' => $kuwaitiCase->management_notes,
        ]);
    }

    public function destroy(Request $request, KuwaitiCase $kuwaitiCase)
    {
        $this->authorize('delete', KuwaitiCase::class);
        $this->denyBranchUser($request);
        $kuwaitiCase->delete();

        return $request->ajax()
            ? response()->json(['message' => 'تم حذف الحالة بنجاح'])
            : redirect()->back()->with('success', 'تم حذف الحالة بنجاح');
    }

    public function archive(Request $request)
    {
        $this->authorize('archive', KuwaitiCase::class);
        $this->denyBranchUser($request);
        $data = $request->validate([
            'id' => ['required', 'integer'],
            'type' => ['required', Rule::in(['archived', 'restore'])],
        ]);

        $case = KuwaitiCase::findOrFail($data['id']);
        $case->archived = $data['type'] === 'archived' ? 1 : 0;
        $case->save();

        return response()->json(['id' => $case->id, 'archived' => (int) $case->archived]);
    }

    public function import(Request $request)
    {
        $this->authorize('import', KuwaitiCase::class);
        $this->denyBranchUser($request);
        $request->validate([
            'file' => ['required', 'file'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ]);

        try {
            $import = new KuwaitiCasesImport($request->filled('branch_id') ? (int) $request->input('branch_id') : null);
            Excel::import($import, $request->file('file'));
        } catch (\RuntimeException $e) {
            return redirect()->route('kuwaiti-cases.index')->with('error', 'تعذر قراءة الملف: ' . $e->getMessage());
        }

        return redirect()->route('kuwaiti-cases.index')->with('success', 'تم استيراد ' . ($import->imported ?? 0) . ' حالة بنجاح' . (($import->skipped ?? 0) ? ' (تم تخطي ' . $import->skipped . ' صف)' : ''));
    }

    public function export(Request $request)
    {
        $this->authorize('export', KuwaitiCase::class);
        $data = $request->filled('filters')
            ? $this->caseRows($this->filteredQuery($this->filtersRequest($request), (int) $request->input('archived', 0))->get())->all()
            : json_decode((string) $request->data, true);

        return Excel::download(new KuwaitiCasesExport(collect($data)), 'حالات الكويتي ' . Carbon::now()->format('Y-m-d H-i-s') . '.xlsx');
    }

    public function filterOptions(Request $request)
    {
        $this->authorize('view', KuwaitiCase::class);
        $column = $request->string('column')->toString();
        if (! array_key_exists($column, $this->filterableColumns())) {
            return response()->json(['values' => [], 'truncated' => false]);
        }

        $limit = min(max((int) $request->input('limit', 5000), 1), 5000);
        $expr = $this->columnExpression($column);
        $query = KuwaitiCase::query()
            ->leftJoin('branches', 'branches.id', '=', 'kuwaiti_cases.branch_id')
            ->where('kuwaiti_cases.archived', (int) $request->boolean('archived'));
        $this->applyBaseFilters($query, $request, false);
        $query->whereNotNull($expr)->where($expr, '<>', '');
        if ($request->filled('q')) {
            $query->where($expr, 'like', '%' . $this->escapeLike($request->input('q')) . '%');
        }
        $values = $query->distinct()->orderBy($expr)->limit($limit + 1)->pluck($expr);

        return response()->json(['values' => $values->take($limit)->values(), 'truncated' => $values->count() > $limit]);
    }

    // ===== الاستعلام والفلاتر =====

    private function filteredQuery(Request $request, int $archived): Builder
    {
        $query = KuwaitiCase::query()
            ->leftJoin('branches', 'branches.id', '=', 'kuwaiti_cases.branch_id')
            ->select('kuwaiti_cases.*', 'branches.name as branch_name_sql')
            ->where('kuwaiti_cases.archived', $archived);

        $this->applyBaseFilters($query, $request, true);

        return $query->orderBy('kuwaiti_cases.date')->orderBy('kuwaiti_cases.id');
    }

    /** $withColumnFilters = false لقوائم الفلاتر: كل عمود يعرض كل قيمه بدون تأثير فلاتر الأعمدة الأخرى. */
    private function applyBaseFilters(Builder $query, Request $request, bool $withSearch): void
    {
        if ($request->filled('year') && $request->input('year') !== 'all') {
            $query->whereYear('kuwaiti_cases.date', (int) $request->input('year'));
        }
        if ($request->filled('from_date')) $query->whereDate('kuwaiti_cases.date', '>=', $request->input('from_date'));
        if ($request->filled('to_date')) $query->whereDate('kuwaiti_cases.date', '<=', $request->input('to_date'));
        if ($request->filled('from_score')) $query->where('kuwaiti_cases.total_score', '>=', (int) $request->input('from_score'));
        if ($request->filled('to_score')) $query->where('kuwaiti_cases.total_score', '<=', (int) $request->input('to_score'));

        if (! $withSearch) {
            return;
        }
        if ($request->filled('q')) {
            $like = '%' . $this->escapeLike($request->input('q')) . '%';
            $query->where(function (Builder $q) use ($like) {
                foreach (['name', 'patient_ID', 'phone_number', 'exam', 'address'] as $column) {
                    $q->orWhere('kuwaiti_cases.' . $column, 'like', $like);
                }
            });
        }
        $this->applyColumnFilters($query, $request->input('filters', []), $request->input('match') === 'any');
    }

    /** $any = true: شروط الأعمدة تُضم بـ OR، وإلا فكلها AND. السنة والبحث والتاريخ والنقاط تبقى AND دائمًا. */
    private function applyColumnFilters(Builder $query, mixed $filters, bool $any = false): void
    {
        if (is_string($filters)) $filters = json_decode($filters, true) ?: [];
        $types = $this->filterableColumns();
        $orGroup = [];
        foreach ((array) $filters as $column => $value) {
            if (! isset($types[$column])) continue;
            $qualified = $this->columnExpression($column);
            $filter = is_array($value) ? $value : ['values' => [$value]];
            $values = $filter['values'] ?? (array_key_exists('value', $filter) ? [$filter['value']] : $filter);
            $values = array_values(array_filter((array) $values, fn ($item) => $item !== '' && $item !== null));
            $empty = filter_var($filter['empty'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $nonEmpty = $types[$column] === 'list' && filter_var($filter['nonempty'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $contains = $types[$column] === 'list' ? trim((string) ($filter['contains'] ?? '')) : '';
            if ($types[$column] === 'boolean') {
                $values = array_values(array_intersect(array_map('strval', $values), ['0', '1']));
            } elseif ($types[$column] !== 'list') {
                $values = array_values(array_intersect(array_map('strval', $values), array_map('strval', $this->enumKeys($types[$column]))));
            }
            if (! $values && $contains === '' && ! $empty && ! $nonEmpty) continue;
            $isBoolean = $types[$column] === 'boolean';
            $condition = function (Builder $q) use ($qualified, $values, $contains, $empty, $isBoolean, $nonEmpty) {
                if ($nonEmpty) $q->orWhere(fn (Builder $e) => $e->whereNotNull($qualified)->where($qualified, '<>', ''));
                if ($values) $q->orWhereIn($qualified, $values);
                if ($contains !== '') $q->orWhere($qualified, 'like', '%' . $this->escapeLike($contains) . '%');
                if ($empty) {
                    $q->orWhere(function (Builder $e) use ($qualified, $isBoolean) {
                        $e->whereNull($qualified);
                        if (! $isBoolean) $e->orWhere($qualified, '');
                    });
                }
            };
            if ($any) $orGroup[] = $condition; else $query->where($condition);
        }
        if ($orGroup) {
            $query->where(function (Builder $q) use ($orGroup) {
                foreach ($orGroup as $condition) $q->orWhere($condition);
            });
        }
    }

    private function filterableColumns(): array
    {
        return [
            'name' => 'list', 'patient_ID' => 'list', 'phone_number' => 'list', 'address' => 'list',
            'displacement_address' => 'list', 'exam' => 'list', 'exam_cost' => 'list', 'total_score' => 'list',
            'data_entry' => 'list', 'branch_name' => 'list', 'researcher_opinion' => 'list', 'management_notes' => 'list',
            'gender' => 'gender', 'marital_status' => 'marital_status', 'exam_need' => 'exam_need',
            'housing_status' => 'housing_status', 'income_status' => 'income_status', 'family_burden' => 'family_burden',
            'need_percentage' => 'need_percentage', 'management_status' => 'management_status',
            'eligible' => 'boolean',
        ];
    }

    private function enumKeys(string $type): array
    {
        return match ($type) {
            'gender' => array_keys(KuwaitiCase::GENDERS),
            'marital_status' => array_keys(KuwaitiCase::MARITAL_STATUSES),
            'exam_need' => array_keys(KuwaitiCase::EXAM_NEEDS),
            'housing_status' => array_keys(KuwaitiCase::HOUSING_STATUSES),
            'income_status' => array_keys(KuwaitiCase::INCOME_STATUSES),
            'family_burden' => array_keys(KuwaitiCase::FAMILY_BURDENS),
            'need_percentage' => array_keys(KuwaitiCase::NEED_PERCENTAGES),
            'management_status' => array_keys(Record::MANAGEMENT_STATUSES),
            default => [],
        };
    }

    private function columnExpression(string $column): string
    {
        return match ($column) {
            'branch_name' => 'branches.name',
            default => 'kuwaiti_cases.' . $column,
        };
    }

    private function dataTableMeta(Request $request, int $archived): array
    {
        $query = $this->filteredQuery($request, $archived);
        $totalsQuery = clone $query;
        $totalsQuery->reorder()->getQuery()->columns = null;
        $totals = $totalsQuery->selectRaw('COALESCE(SUM(kuwaiti_cases.exam_cost),0) exam_cost')->first()->toArray();

        return ['count' => (clone $query)->count(), 'totals' => Arr::only($totals, ['exam_cost'])];
    }

    private function caseYears(int $archived): array
    {
        return KuwaitiCase::query()->where('archived', $archived)->whereNotNull('date')
            ->selectRaw('DISTINCT YEAR(date) as year')->orderByDesc('year')->pluck('year')->all();
    }

    private function filtersRequest(Request $request): Request
    {
        $filters = json_decode((string) $request->input('filters'), true) ?: [];

        return Request::create('/', 'GET', array_merge($request->only(['year', 'q', 'match', 'from_date', 'to_date', 'from_score', 'to_score']), ['filters' => $filters]));
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    // ===== التحقق والصلاحيات =====

    private function coreRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'date' => ['required', 'date'],
            'gender' => ['required', Rule::in(array_keys(KuwaitiCase::GENDERS))],
            'patient_ID' => ['required', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'marital_status' => ['nullable', Rule::in(array_keys(KuwaitiCase::MARITAL_STATUSES))],
            'address' => ['nullable', 'string', 'max:255'],
            'displacement_address' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:30'],
            'exam' => ['required', 'string', 'max:255'],
            'exam_cost' => ['nullable', 'numeric', 'min:0'],
            'exam_need' => ['required', Rule::in(array_keys(KuwaitiCase::EXAM_NEEDS))],
            'housing_status' => ['required', Rule::in(array_keys(KuwaitiCase::HOUSING_STATUSES))],
            'income_status' => ['required', Rule::in(array_keys(KuwaitiCase::INCOME_STATUSES))],
            'family_burden' => ['required', Rule::in(array_keys(KuwaitiCase::FAMILY_BURDENS))],
            'data_entry' => ['nullable', 'string', 'max:255'],
            'case_description' => ['nullable', 'string', 'max:5000'],
            'branch_id' => ['nullable', 'integer', 'exists:branches,id'],
        ];
    }

    /** حقول موافقة الإدارة تُقبل فقط من مستخدم يملك صلاحية kuwaiticases.management، وإلا تُتجاهل. */
    private function managementFields(Request $request): array
    {
        if (! $request->user()->can('management', KuwaitiCase::class) || ! $request->hasAny(['management_status', 'management_notes'])) {
            return [];
        }
        $request->merge(['management_status' => $request->input('management_status') === '' ? null : $request->input('management_status')]);
        $data = $request->validate([
            'management_status' => ['nullable', Rule::in(array_keys(Record::MANAGEMENT_STATUSES))],
            'management_notes' => ['nullable', 'string', 'max:2000'],
        ]);

        return [
            'management_status' => $data['management_status'] ?? null,
            'management_notes' => $data['management_notes'] ?? null,
        ];
    }

    private function denyBranchUser(Request $request): void
    {
        abort_if($request->user()->isBranchUser(), 403);
    }

    // ===== صفوف JSON (للتعديل والتصدير) =====

    private function rowData(KuwaitiCase $case, ?int $index = null): array
    {
        $row = array_merge($case->toArray(), [
            'gender_label' => $case->gender_label,
            'marital_status_label' => $case->marital_status_label,
            'exam_need_label' => $case->exam_need_label,
            'housing_status_label' => $case->housing_status_label,
            'income_status_label' => $case->income_status_label,
            'family_burden_label' => $case->family_burden_label,
            'branch_name' => Arr::get($case->getAttributes(), 'branch_name_sql') ?? $case->branch?->name,
            'eligible_label' => $case->eligible_label,
            'need_percentage_label' => $case->need_percentage_label,
            'management_status_label' => $case->management_status_label,
        ]);
        if ($index !== null) {
            $row['DT_RowIndex'] = $index;
        }

        return $row;
    }

    private function caseRows(Collection $cases): Collection
    {
        return $cases->values()->map(fn (KuwaitiCase $case, int $index) => $this->rowData($case, $index + 1));
    }
}
