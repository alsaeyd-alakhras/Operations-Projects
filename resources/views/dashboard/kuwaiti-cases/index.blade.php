@php
    use App\Models\KuwaitiCase;
    $archived = (int) ($archived ?? 0);
    $isBranch = auth()->user()->isBranchUser();
    $labels = fn (array $options) => collect($options)->map(fn ($option) => $option['label'])->all();
@endphp
<x-front-layout>
    @push('styles')
        <!-- DataTables CSS -->
        <link rel="stylesheet" href="{{asset('css/datatable/jquery.dataTables.min.css')}}">
        <link rel="stylesheet" href="{{asset('css/datatable/dataTables.bootstrap4.css')}}">
        <link rel="stylesheet" href="{{asset('css/datatable/dataTables.dataTables.css')}}">
        <link rel="stylesheet" href="{{asset('css/datatable/buttons.dataTables.css')}}">

        <link id="stickyTableLight" rel="stylesheet" href="{{ asset('css/stickyTable.css') }}">
        <link id="stickyTableDark" rel="stylesheet" href="{{ asset('css/stickyTableDark.css') }}" disabled>
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/datatableIndex.css') }}">
        <link rel="stylesheet" href="{{ asset('css/records.css') }}">
        <link rel="stylesheet" href="{{ asset('css/kuwaiti.css') }}">
    @endpush
    <x-slot:extra_nav>
        @can('create','App\\Models\KuwaitiCase')
        @if (!$isBranch && !$archived)
        <li class="nav-item mx-1 d-flex align-items-center"><button type="button" class="rec-tool rec-tool--icon rec-tool--primary" id="createNew" title="إضافة حالة" aria-label="إضافة حالة"><i class="fe fe-plus"></i></button></li>
        @endif
        @endcan
        <li class="nav-item dropdown mx-1 d-flex align-items-center">
            <button class="rec-tool rec-tool--icon" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="بحث" aria-label="بحث">
                <i class="fe fe-search"></i><span class="records-search-active d-none"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-right rec-menu records-search-menu">
                <label for="records-year">السنة</label>
                <select id="records-year" class="form-control">
                    <option value="all">الكل</option>
                    @foreach ($years as $year)
                        <option value="{{ $year }}">{{ $year }}</option>
                    @endforeach
                </select>
                <label for="records-q">بحث عام</label>
                <input id="records-q" type="search" class="form-control" placeholder="اسم، هوية، جوال، فحص، عنوان..." autocomplete="off">
                <button type="button" id="records-search-apply" class="rec-btn rec-btn--primary w-100"><i class="fe fe-search"></i> بحث</button>
            </div>
        </li>
        @can('export','App\\Models\KuwaitiCase')
        <li class="nav-item mx-1 d-flex align-items-center">
            <button type="button" class="rec-tool rec-tool--icon" id="excel-export" title="تصدير Excel" aria-label="تصدير Excel"><i class="fe fe-download"></i></button>
        </li>
        @endcan
        @can('archive','App\\Models\KuwaitiCase')
        <li class="nav-item mx-1 d-flex align-items-center">
            @if ($archived)
                <a class="rec-tool kw-switch" href="{{ route('kuwaiti-cases.index') }}" title="الحالات الحالية"><i class="fe fe-layers"></i><span class="ml-1">الحالات</span></a>
            @else
                <a class="rec-tool kw-switch" href="{{ route('kuwaiti-cases.index', ['archived' => 1]) }}" title="الأرشيف"><i class="fe fe-archive"></i><span class="ml-1">الأرشيف</span></a>
            @endif
        </li>
        @endcan
        <li class="nav-item mx-1 d-flex align-items-center">
            <button type="button" class="rec-tool rec-tool--icon" id="records-search-reset" title="مسح الفلاتر" aria-label="مسح الفلاتر"><i class="fe fe-rotate-ccw"></i></button>
        </li>
        <li class="nav-item mx-1 d-flex align-items-center">
            <button type="button" class="rec-tool rec-tool--icon" id="refreshData" title="تحديث" aria-label="تحديث"><i class="fe fe-refresh-ccw"></i></button>
        </li>
    </x-slot:extra_nav>
    <div class="records-page">
    <div class="rec-card">
        <div class="rec-table-scroll"><table id="records-table" class="table table-striped table-hover sticky" style="width:100%">
            <thead>
<tr>
<th class="text-white text-center" data-label="الترقيم">#</th>
<th>@include('dashboard.records._th-filter', ['col' => 'done', 'label' => 'تمت', 'type' => 'bool'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'date', 'label' => 'التاريخ', 'type' => 'date'])</th>
<th class="sticky">@include('dashboard.records._th-filter', ['col' => 'name', 'label' => 'اسم المريض', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'patient_ID', 'label' => 'رقم الهوية', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'gender', 'label' => 'الجنس', 'type' => 'enum', 'options' => KuwaitiCase::GENDERS])</th>
<th data-label="العمر">العمر</th>
<th>@include('dashboard.records._th-filter', ['col' => 'marital_status', 'label' => 'الحالة الاجتماعية', 'type' => 'enum', 'options' => KuwaitiCase::MARITAL_STATUSES])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'phone_number', 'label' => 'رقم التواصل', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'address', 'label' => 'العنوان الحالي', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'displacement_address', 'label' => 'عنوان السكن قبل النزوح', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'exam', 'label' => 'الفحص المطلوب', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'exam_cost', 'label' => 'تكلفة الفحص', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'exam_need', 'label' => 'الحاجة للفحص', 'type' => 'enum', 'options' => $labels(KuwaitiCase::EXAM_NEEDS)])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'housing_status', 'label' => 'حالة النزوح والسكن', 'type' => 'enum', 'options' => $labels(KuwaitiCase::HOUSING_STATUSES)])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'income_status', 'label' => 'مصادر الدخل', 'type' => 'enum', 'options' => $labels(KuwaitiCase::INCOME_STATUSES)])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'family_burden', 'label' => 'حجم الأسرة', 'type' => 'enum', 'options' => $labels(KuwaitiCase::FAMILY_BURDENS)])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'total_score', 'label' => 'النقاط', 'type' => 'range', 'rangeKey' => 'score'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'branch_name', 'label' => 'الفرع', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'eligible', 'label' => 'رأي مدير المكتب', 'type' => 'enum', 'options' => ['1' => 'يستحق', '0' => 'لا يستحق']])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'need_percentage', 'label' => 'نسبة الاحتياج', 'type' => 'enum', 'options' => KuwaitiCase::NEED_PERCENTAGES])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'branch_notes', 'label' => 'ملاحظات الفرع', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'management_status', 'label' => 'موافقة الإدارة', 'type' => 'enum', 'options' => \App\Models\Record::MANAGEMENT_STATUSES])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'data_entry', 'label' => 'مدخل البيانات', 'type' => 'list'])</th>
<th class="rec-actions">إجراءات</th>
</tr>
</thead>
            <tfoot>
<tr>
                <td class="text-white opacity-8 text-center" id="count_records"></td>
                <td></td>
                <td></td>
                <td class='sticky text-right'>المجموع</td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class='text-white' id="total_exam_cost"></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td class="rec-actions"></td>
</tr>
</tfoot>
        </table></div>
    </div>

    <div class="modal fade" id="editKuwaiti" tabindex="-1" role="dialog" aria-labelledby="recordFormTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="editForm" class="rec-form" enctype="multipart/form-data">
                    @include('dashboard.kuwaiti-cases._modal')
                </form>
            </div>
        </div>
    </div>

    @can('import','App\\Models\KuwaitiCase')
    @if (!$isBranch)
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="importExcelTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelTitle">استيراد من ملف Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('kuwaiti-cases.import') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group col-md-12">
                            <label for="file">إختيار الملف (Excel المصدَّر من Microsoft Forms)</label>
                            <input type="file" name="file" id="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        </div>
                        <div class="form-group col-md-12">
                            <label for="import_branch_id">الفرع (يُطبّق على كل الحالات المستوردة)</label>
                            <select name="branch_id" id="import_branch_id" class="form-control">
                                <option value="">بدون فرع</option>
                                @foreach (\App\Models\Branch::orderBy('name')->get() as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info">استيراد</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @endif
    @endcan

    <form action="{{ route('kuwaiti-cases.export') }}" id="exportExcelForm" style="display: none;" method="post" target="_blank">
        @csrf
        <input type="hidden" name="data" value="">
    </form>
    </div>

    @push('scripts')
        <!-- DataTables JS -->
        <script src="{{asset('js/datatable/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('js/datatable/dataTables.js')}}"></script>
        <script src="{{ asset('js/records-table.js') }}"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                const isBranchUser = @json($isBranch);
                const isArchived = {{ $archived ? 'true' : 'false' }};
                const canManage = @json(auth()->user()->can('management', 'App\\Models\\KuwaitiCase'));
                const canDone = @json(auth()->user()->can('done', 'App\\Models\\KuwaitiCase'));
                const currentUserName = @json(auth()->user()->name);
                const radioFields = ['exam_need', 'housing_status', 'income_status', 'family_burden'];
                const branchFields = ['eligible', 'need_percentage', 'branch_notes'];
                const mgmtFields = ['management_status', 'management_notes'];
                const toast = (message, type) => RecordsTable.toast(message, type);
                $.validator.messages.required = "هذا الحقل مطلوب";
                $("#editForm").validate({
                    ignore: ':hidden, :disabled',
                    rules: { name: { required: true, maxlength: 255 } },
                    messages: { name: "يرجى إدخال اسم المريض" }
                });
                const formatNumber = (number, min = 0) => {
                    if (number === null || number === undefined || number === '' || isNaN(number)) return '';
                    return new Intl.NumberFormat('en-US', { minimumFractionDigits: min, maximumFractionDigits: 2 }).format(number);
                };
                const d10 = (value) => (value ? String(value).substring(0, 10) : '');
                const blank = (value) => (value === null || value === undefined ? '' : value);

                const table = RecordsTable.init({
                    archived: isArchived ? 1 : 0,
                    userId: {{ (int) auth()->id() }},
                    storageKey: 'kw-cols:{{ (int) auth()->id() }}:' + (isArchived ? 'archived' : 'index'),
                    totals: ['exam_cost'],
                    range: { from: 'from_score', to: 'to_score' },
                    canFinancial: true,
                    financialColumns: [],
                    urls: {
                        index: '{{ route("kuwaiti-cases.index", ["archived" => $archived]) }}',
                        filterOptions: '{{ route("kuwaiti-cases.filterOptions") }}'
                    },
                    languageUrl: "{{ asset('files/Arabic.json') }}",
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, className: 'text-center' },
                        { data: 'done', name: 'done', orderable: false, className: 'text-center', render: function (data, type, row) {
                            const checked = data === true || data === 1 || data === '1';
                            if (type !== 'display') return checked ? 'نعم' : 'لا';
                            return '<input type="checkbox" class="kw-done-check" data-id="' + row.id + '"' + (checked ? ' checked' : '') + (canDone ? '' : ' disabled') + ' aria-label="تمت">';
                        } },
                        { data: 'date', name: 'date', orderable: false },
                        { data: 'name', name: 'name', orderable: false, className: 'sticky' },
                        { data: 'patient_ID', name: 'patient_ID', orderable: false },
                        { data: 'gender', name: 'gender', orderable: false, render: function (data, type, row) { return row.gender_label || ''; } },
                        { data: 'age', name: 'age', orderable: false, className: 'text-center' },
                        { data: 'marital_status', name: 'marital_status', orderable: false, render: function (data, type, row) { return row.marital_status_label || ''; } },
                        { data: 'phone_number', name: 'phone_number', orderable: false },
                        { data: 'address', name: 'address', orderable: false, render: RecordsTable.noteRender },
                        { data: 'displacement_address', name: 'displacement_address', orderable: false, render: RecordsTable.noteRender },
                        { data: 'exam', name: 'exam', orderable: false },
                        { data: 'exam_cost', name: 'exam_cost', orderable: false, className: 'rec-num', render: function (data) { return formatNumber(data, 2); } },
                        { data: 'exam_need', name: 'exam_need', orderable: false, render: function (data, type, row) { return row.exam_need_label || ''; } },
                        { data: 'housing_status', name: 'housing_status', orderable: false, render: function (data, type, row) { return row.housing_status_label || ''; } },
                        { data: 'income_status', name: 'income_status', orderable: false, render: function (data, type, row) { return row.income_status_label || ''; } },
                        { data: 'family_burden', name: 'family_burden', orderable: false, render: function (data, type, row) { return row.family_burden_label || ''; } },
                        { data: 'total_score', name: 'total_score', orderable: false, className: 'text-center', render: function (data, type) {
                            if (data === null || data === undefined || data === '') return '';
                            if (type !== 'display') return data;
                            const cls = data >= 70 ? 'high' : (data >= 40 ? 'mid' : 'low');
                            return '<span class="kw-score kw-score--' + cls + '">' + data + '</span>';
                        } },
                        { data: 'branch_name', name: 'branch_name', orderable: false },
                        { data: 'eligible', name: 'eligible', orderable: false, render: function (data, type, row) {
                            const label = row.eligible_label || '';
                            if (type !== 'display') return label;
                            if (row.eligible === true || row.eligible === 1 || row.eligible === '1') return '<span class="rec-chip rec-chip--green">' + label + '</span>';
                            if (row.eligible === false || row.eligible === 0 || row.eligible === '0') return '<span class="rec-chip rec-chip--red">' + label + '</span>';
                            return '<span class="rec-chip rec-chip--gray">' + (label || 'غير محدد') + '</span>';
                        } },
                        { data: 'need_percentage', name: 'need_percentage', orderable: false, className: 'text-center', render: function (data, type, row) { return row.need_percentage_label || ''; } },
                        { data: 'branch_notes', name: 'branch_notes', orderable: false, render: RecordsTable.noteRender },
                        RecordsTable.managementColumn({ canManage: canManage, url: '{{ route("kuwaiti-cases.management", ":id") }}', csrf: '{{ csrf_token() }}' }),
                        { data: 'data_entry', name: 'data_entry', orderable: false },
                        { data: 'edit', name: 'actions', orderable: false, searchable: false, className: 'rec-actions', render: function (data, type, row) {
                            if (type !== 'display') return '';
                            let html = '';
                            @can('update','App\Models\KuwaitiCase')
                                html += `<button class="btn btn-sm p-1 btn-icon text-primary edit_row" data-id="${data}" title="تعديل"><i class="fe fe-edit"></i></button>`;
                            @endcan
                            if (!isBranchUser) {
                                @can('archive','App\Models\KuwaitiCase')
                                    html += isArchived
                                        ? `<button class="btn btn-sm p-1 btn-icon text-primary archived_row" data-type="restore" data-id="${data}" title="استعادة"><i class="fe fe-rotate-ccw"></i></button>`
                                        : `<button class="btn btn-sm p-1 btn-icon text-primary archived_row" data-type="archived" data-id="${data}" title="أرشفة"><i class="fe fe-archive"></i></button>`;
                                @endcan
                                @can('delete','App\Models\KuwaitiCase')
                                    html += `<button class="btn btn-icon p-1 text-danger delete_row" data-id="${data}" title="حذف"><i class="fe fe-trash"></i></button>`;
                                @endcan
                            }
                            return html;
                        } },
                    ]
                });

                // ===== «تمت»: حفظ مباشر من الجدول (مثل موافقة الإدارة) =====
                $(document).off('change.kwdone').on('change.kwdone', '.kw-done-check', function () {
                    if (!canDone) return;
                    const $box = $(this), want = this.checked, row = table.row($box.closest('tr'));
                    $box.prop('disabled', true);
                    $.ajax({
                        url: '{{ route("kuwaiti-cases.done", ":id") }}'.replace(':id', $box.data('id')),
                        method: 'POST', dataType: 'json',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { done: want ? 1 : 0 }
                    }).done(function (res) {
                        const saved = res && res.done !== undefined ? !!res.done : want;
                        const rowData = row.data();
                        if (rowData) rowData.done = saved;
                        $box.prop('checked', saved);
                        // الحالة انتقلت بين القائمة الأساسية والأرشيف: نحدّث الجدول لتختفي من هذه القائمة
                        toast((res && res.message) || 'تم حفظ حالة تمت');
                        if (res && res.archived !== undefined && !!res.archived !== !!isArchived) table.ajax.reload(null, false);
                    }).fail(function () {
                        $box.prop('checked', !want);
                        toast('تعذر حفظ حالة تمت', 'error');
                    }).always(function () { $box.prop('disabled', false); });
                });

                // ===== live total of the 4 evaluation groups =====
                function recalcTotal() {
                    let total = 0;
                    radioFields.forEach(function (field) {
                        const $checked = $('input[name="' + field + '"]:checked');
                        if ($checked.length) total += parseInt($checked.data('points'), 10) || 0;
                    });
                    $('#kw_total_score').text(total);
                    return total;
                }
                $(document).on('change', '.kw-cards input[type="radio"]', recalcTotal);

                const record = {
                    id: '', date: '', name: '', gender: '', patient_ID: '', birth_date: '', marital_status: '',
                    address: '', displacement_address: '', phone_number: '', exam: '', exam_cost: '',
                    exam_need: '', housing_status: '', income_status: '', family_burden: '',
                    case_description: '', data_entry: '', branch_id: '',
                    eligible: '', need_percentage: '', branch_notes: '',
                    management_status: '', management_notes: '', done: ''
                };
                function fillRecord(response) {
                    response = response || {};
                    $.each(record, function (key) { record[key] = blank(response[key]); });
                    record.id = blank(response.id);
                    record.date = d10(response.date);
                    record.birth_date = d10(response.birth_date);
                    record.eligible = response.eligible === null || response.eligible === undefined || response.eligible === '' ? '' : (response.eligible ? '1' : '0');
                    record.need_percentage = response.need_percentage === null || response.need_percentage === undefined ? '' : String(response.need_percentage);
                    record.done = response.done === true || response.done === 1 || response.done === '1' ? 1 : 0;
                    $('#done').prop('checked', record.done === 1);
                    $.each(record, function (key, value) {
                        if (radioFields.indexOf(key) !== -1 || key === 'done') return;
                        const input = $('#' + key);
                        if (input.length) input.val(value);
                    });
                    radioFields.forEach(function (field) {
                        const $radios = $('input[name="' + field + '"]').prop('checked', false);
                        if (record[field] !== '') $radios.filter('[value="' + record[field] + '"]').prop('checked', true);
                    });
                    recalcTotal();
                }
                function collectRecord() {
                    $.each(record, function (key) {
                        if (key === 'id') return;
                        if (key === 'done') { record.done = $('#done').is(':checked') ? 1 : 0; return; }
                        if (radioFields.indexOf(key) !== -1) { record[key] = $('input[name="' + key + '"]:checked').val() || ''; return; }
                        const input = $('#' + key);
                        if (input.length) record[key] = input.val();
                    });
                    return record;
                }

                // ===== delete / archive =====
                $(document).on('click', '.delete_row', function () {
                    const id = $(this).data('id');
                    if (!confirm('هل أنت متأكد من حذف الحالة؟')) return;
                    $.ajax({
                        url: '{{ route("kuwaiti-cases.destroy", ":id") }}'.replace(':id', id),
                        method: 'DELETE',
                        data: { "_token": "{{ csrf_token() }}" },
                        success: function () { toast('تم حذف الحالة بنجاح'); table.ajax.reload(null, false); },
                        error: function () { toast('هنالك خطأ في عملية الحذف.', 'error'); }
                    });
                });
                $(document).on('click', '.archived_row', function () {
                    $.ajax({
                        url: "{{ route('kuwaiti-cases.archive') }}",
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: { id: $(this).data('id'), type: $(this).data('type') },
                        success: function () { toast('تم تحديث حالة الأرشفة'); table.ajax.reload(null, false); },
                        error: function () { toast('هنالك خطأ في الإتصال بالسيرفر.', 'error'); }
                    });
                });

                // ===== edit =====
                $(document).on('click', '.edit_row', function () {
                    const id = $(this).data('id');
                    $.ajax({
                        url: '{{ route("kuwaiti-cases.edit", ":id") }}'.replace(':id', id),
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        success: function (response) {
                            fillRecord(response);
                            $('#addRecord').remove();
                            $('#update').remove();
                            $('#recordFormTitle').text('تعديل الحالة');
                            $('#btns_form').append('<button type="button" id="update" class="btn rec-btn rec-btn--primary"><i class="fe fe-check"></i> حفظ التعديلات</button>');
                            $('#editKuwaiti').modal('show');
                        },
                        error: function () { toast('هنالك خطأ في الإتصال بالسيرفر.', 'error'); }
                    });
                });
                $(document).on('click', '#update', function () {
                    const form = $('#editForm');
                    if (!form.validate().form()) {
                        $('#alerts').text('يرجى تصحيح الأخطاء في النموذج قبل المتابعة.').slideDown();
                        return;
                    }
                    collectRecord();
                    let data = { ...record };
                    if (isBranchUser) {
                        data = { eligible: record.eligible, need_percentage: record.need_percentage, branch_notes: record.branch_notes, management_status: record.management_status, management_notes: record.management_notes };
                        if (canDone) data.done = record.done;
                    } else {
                        branchFields.forEach(function (field) { delete data[field]; });
                    }
                    if (!canManage) mgmtFields.forEach(function (field) { delete data[field]; });
                    if (!canDone) delete data.done;
                    $.ajax({
                        url: "{{ route('kuwaiti-cases.update', ':id') }}".replace(':id', record.id),
                        method: 'PUT',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: data,
                        success: function (res) {
                            $('#editKuwaiti').modal('hide');
                            table.ajax.reload(null, false);
                            toast((res && res.done_changed && res.message) ? res.message : 'تم التعديل بنجاح');
                        },
                        error: function () { $('#alerts').text('هنالك خطأ في إدخال الداتا يرجى التحقق منها.').slideDown(); }
                    });
                    $('#alerts').slideUp();
                });

                // ===== create =====
                $(document).on('click', '#createNew', function () {
                    $.ajax({
                        url: '{{ route("kuwaiti-cases.create") }}',
                        method: 'GET',
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                        success: function (response) {
                            fillRecord(response);
                            if (!$('#data_entry').val()) $('#data_entry').val(currentUserName);
                            $('#addRecord').remove();
                            $('#update').remove();
                            $('#recordFormTitle').text('إضافة حالة جديدة');
                            $('#btns_form').append('<button type="button" id="addRecord" class="btn rec-btn rec-btn--primary"><i class="fe fe-plus"></i> إضافة الحالة</button>');
                            $('#alerts').hide();
                            $('#editKuwaiti').modal('show');
                        },
                        error: function () { toast('هنالك خطأ في الإتصال بالسيرفر.', 'error'); }
                    });
                });
                $(document).on('click', '#addRecord', function () {
                    const form = $('#editForm');
                    if (!form.validate().form()) {
                        $('#alerts').text('يرجى تصحيح الأخطاء في النموذج قبل المتابعة.').slideDown();
                        return;
                    }
                    collectRecord();
                    const data = { ...record };
                    delete data.id;
                    branchFields.forEach(function (field) { delete data[field]; });
                    mgmtFields.forEach(function (field) { delete data[field]; });
                    if (!canDone) delete data.done;
                    $.ajax({
                        url: "{{ route('kuwaiti-cases.store') }}",
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        data: data,
                        success: function () {
                            $('#editKuwaiti').modal('hide');
                            table.ajax.reload(null, false);
                            toast('تم إضافة حالة جديدة بنجاح');
                        },
                        error: function () { $('#alerts').text('هنالك خطأ في إدخال الداتا يرجى التحقق منها.').slideDown(); }
                    });
                    $('#alerts').slideUp();
                });

                $(document).on('click', '#import_excel_btn', function () {
                    $('#editKuwaiti').modal('hide');
                    $('#importExcel').modal('show');
                });
            });
        </script>
        <script>
            $(document).ready(function() {
                document.documentElement.classList.toggle('rec-dark', curentTheme === 'dark');
                if (curentTheme == "light") {
                    $('#stickyTableLight').prop('disabled', false);
                    $('#stickyTableDark').prop('disabled', true);
                } else {
                    $('#stickyTableLight').prop('disabled', true);
                    $('#stickyTableDark').prop('disabled', false);
                }
            });
        </script>
    @endpush
</x-front-layout>
