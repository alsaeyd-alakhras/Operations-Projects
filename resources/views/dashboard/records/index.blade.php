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
    @endpush
    <x-slot:extra_nav>
        @can('create','App\\Models\Record')
        @if (!auth()->user()->isBranchUser())
        <li class="nav-item mx-1 d-flex align-items-center"><button type="button" class="rec-tool rec-tool--icon rec-tool--primary" id="createNew" title="إضافة سجل" aria-label="إضافة سجل"><i class="fe fe-plus"></i></button></li>
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
                <input id="records-q" type="search" class="form-control" placeholder="اسم، هوية، جوال، عملية، طبيب..." autocomplete="off">
                <button type="button" id="records-search-apply" class="rec-btn rec-btn--primary w-100"><i class="fe fe-search"></i> بحث</button>
            </div>
        </li>
        @canany(['export','exportexcel'],'App\\Models\Record')
        <li class="nav-item dropdown mx-1 d-flex align-items-center">
            <button class="rec-tool rec-tool--icon" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="تصدير" aria-label="تصدير"><i class="fe fe-download"></i></button>
            <div class="dropdown-menu dropdown-menu-right rec-menu">
                @can('export','App\\Models\Record')
                <button class="dropdown-item" id="print-report"><i class="fe fe-printer"></i> تقرير PDF</button>
                @endcan
                @can('exportexcel','App\\Models\Record')
                <button class="dropdown-item" id="excel-export"><i class="fe fe-file-text"></i> ملف Excel</button>
                @endcan
            </div>
        </li>
        @endcanany
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
<th>@include('dashboard.records._th-filter', ['col' => 'financier', 'label' => 'اسم الممول', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'financier_number', 'label' => 'رقم الممول', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'age', 'label' => 'العمر', 'type' => 'range'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'patient_ID', 'label' => 'رقم الهوية', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'phone_number1', 'label' => 'جوال 1', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'phone_number2', 'label' => 'جوال 2', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'operation', 'label' => 'العملية', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'doctor', 'label' => 'الطبيب', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'eligible', 'label' => 'يستحق', 'type' => 'bool'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'branch_name', 'label' => 'الفرع', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'branch_notes', 'label' => 'ملاحظات الفرع', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'management_status', 'label' => 'موافقة الإدارة', 'type' => 'enum', 'options' => \App\Models\Record::MANAGEMENT_STATUSES])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'amount', 'label' => 'التكلفة', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'doctor_share', 'label' => 'حصة الطبيب', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'anesthesia', 'label' => 'طبيب التخدير', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'anesthesiologists_share', 'label' => 'حصة التخدير', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'bed', 'label' => 'المبيت', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'private', 'label' => 'خاص', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'address', 'label' => 'العنوان', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'displacement_status', 'label' => 'حالة النزوح والسكن وتضرر البيت', 'type' => 'enum', 'options' => \App\Models\Record::DISPLACEMENT_STATUSES])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'income_status', 'label' => 'مصادر الدخل والوضع المالي', 'type' => 'enum', 'options' => \App\Models\Record::INCOME_STATUSES])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'family_burden', 'label' => 'حجم الأسرة والحالات الخاصة', 'type' => 'enum', 'options' => \App\Models\Record::FAMILY_BURDENS])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'distributed', 'label' => 'تم التوزيع', 'type' => 'bool'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'notes', 'label' => 'ملاحظات', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'notes_2', 'label' => 'ملاحظات ثانوية', 'type' => 'list'])</th>
<th>@include('dashboard.records._th-filter', ['col' => 'user_name', 'label' => 'المستخدم', 'type' => 'list'])</th>
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
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td class='text-white' id="total_amount"></td>
                                <td class='text-white' id="total_doctor_share"></td>
                                <td></td>
                                <td class='text-white' id="total_anesthesiologists_share"></td>
                                <td class='text-white' id="total_bed"></td>
                                <td class='text-white' id="total_private"></td>
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
    <div class="modal fade" id="editRecord" tabindex="-1" role="dialog" aria-labelledby="recordFormTitle" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="editForm" class="rec-form" enctype="multipart/form-data">
                    @include('dashboard.records.editModal')
                </form>
            </div>
        </div>
    </div>

    @can('import','App\\Models\Record')
    @if (!auth()->user()->isBranchUser())
    <div class="modal fade" id="importExcel" tabindex="-1" role="dialog" aria-labelledby="importExcelTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="importExcelTitle">استيراد من ملف Excel</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('records.import')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group col-md-12">
                            <label for="report">إختيار الملف</label>
                            <input type="file" name="file" id="file" class="form-control" required>
                        </div>
                        <span>تحميل نموذج الإستيراد
                            @can('financial','App\\Models\Record')
                            <a href="{{asset('files/records_template.xlsx')}}" class="nav-link d-inline" download="نموذج الإستيراد">من هنا</a>
                            @else
                            <a href="{{asset('files/records_template2.xlsx')}}" class="nav-link d-inline" download="نموذج الإستيراد">من هنا</a>
                            @endcan
                        </span>
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

    <div class="modal fade" id="printReport" tabindex="-1" role="dialog" aria-labelledby="printReportTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="printReportTitle">طباعة تقرير</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{route('records.print')}}" method="post" target="_blank">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="data" value="">
                        <div class="form-group col-md-12">
                            <label for="report">نوع التقرير</label>
                            <select name="report" id="report" class="form-control" required>
                                <option value="" disabled selected>اختر</option>
                                <option value="basic">أساسي</option>
                                @can('financial','App\\Models\Record')
                                    <option value="mali">مالي</option>
                                @endcan
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-info">طباعة</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <form action="{{route('records.export')}}" id="exportExcelForm" style="display: none;" method="post" target="_blank">
        <div class="modal-body">
            @csrf
            <input type="hidden" name="data" value="">
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-info">طباعة</button>
        </div>
    </form>
    </div>

    @push('scripts')
        <!-- DataTables JS -->
        <script src="{{asset('js/datatable/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('js/datatable/dataTables.js')}}"></script>
        <script src="{{ asset('js/records-table.js') }}"></script>

        
        <script type="text/javascript">
            $(document).ready(function() {
                const isBranchUser = @json(auth()->user()->isBranchUser());
                $.validator.messages.required = "هذا الحقل مطلوب";
                $("#editForm").validate({
                    ignore: ':hidden, :disabled',
                    rules: {
                        name: {
                            required: true,
                            maxlength: 255,
                        }
                    },
                    messages: {
                        name: "يرجى إدخال اسم المريض",
                    }
                });
                let formatNumber = (number,min = 0) => {
                    // التحقق إذا كانت القيمة فارغة أو غير صالحة كرقم
                    if (number === null || number === undefined || isNaN(number)) {
                        return ''; // إرجاع قيمة فارغة إذا كان الرقم غير صالح
                    }
                    return new Intl.NumberFormat('en-US', { minimumFractionDigits: min, maximumFractionDigits: 2 }).format(number);
                };
                const canManage = @json(auth()->user()->can('management', 'App\\Models\\Record'));
                const table = RecordsTable.init({
                    archived: 0,
                    userId: {{ (int) auth()->id() }},
                    defaultHidden: @json(auth()->user()->defaultHiddenRecordColumns()),
                    @if (!is_null(auth()->user()->allowedRecordColumns()))
                    allowedColumns: @json(auth()->user()->allowedRecordColumns()),
                    @endif
                    canFinancial: @json(auth()->user()->can('financial', 'App\\Models\\Record')),
                    financialColumns: ['doctor_share', 'anesthesia', 'anesthesiologists_share', 'bed', 'private'],
                    urls: {
                        index: '{{ route("records.index") }}',
                        filterOptions: '{{ route("records.filterOptions") }}'
                    },
                    languageUrl: "{{ asset('files/Arabic.json') }}",
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, className: 'text-center'}, // عمود الترقيم التلقائي
                        { data: 'done', name: 'done' , orderable: false, className: 'text-center', render: function(data, type, row) {
                            let link = `<input type="checkbox" ${data == 1 ? 'checked' : ''} disabled>`;
                            return link ;
                        }},
                        { data: 'date', name: 'date', orderable: false},
                        { data: 'name', name: 'name', orderable: false, className: 'sticky' },
                        { data: 'financier', name: 'financier', orderable: false, className: 'text-center'},
                        { data: 'financier_number', name: 'financier_number', orderable: false, className: 'text-center'},
                        { data: 'age', name: 'age', orderable: false},
                        { data: 'patient_ID', name: 'patient_ID', orderable: false},
                        { data: 'phone_number1', name: 'phone_number1', orderable: false},
                        { data: 'phone_number2', name: 'phone_number2', orderable: false},
                        { data: 'operation', name: 'operation', orderable: false},
                        { data: 'doctor', name: 'doctor', orderable: false, className: 'text-center'},
                        { data: 'eligible_label', name: 'eligible_label', orderable: false},
                        { data: 'branch_name', name: 'branch_name', orderable: false},
                        { data: 'branch_notes', name: 'branch_notes', orderable: false},
                        RecordsTable.managementColumn({ canManage: @json(auth()->user()->can('management', 'App\Models\Record')), url: '{{ route("records.management", ":id") }}', csrf: '{{ csrf_token() }}' }),
                        { data: 'amount', name: 'amount', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            return  formatNumber(data,2);
                        }},
                        { data: 'doctor_share', name: 'doctor_share', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'anesthesia', name: 'anesthesia', orderable: false},
                        { data: 'anesthesiologists_share', name: 'anesthesiologists_share', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'bed', name: 'bed', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'private', name: 'private', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'address', name: 'address', orderable: false},
                        { data: 'displacement_status', name: 'displacement_status', orderable: false, render: function(data, type, row) { return row.displacement_status_label || ''; }},
                        { data: 'income_status', name: 'income_status', orderable: false, render: function(data, type, row) { return row.income_status_label || ''; }},
                        { data: 'family_burden', name: 'family_burden', orderable: false, render: function(data, type, row) { return row.family_burden_label || ''; }},
                        { data: 'distributed', name: 'distributed', orderable: false, render: function(data, type, row) {
                            if (type === 'display') {
                                return row.distributed_label === 'نعم' ? '<span class="rec-chip rec-chip--yes">نعم</span>' : row.distributed_label === 'لا' ? '<span class="rec-chip rec-chip--no">لا</span>' : '';
                            }
                            return row.distributed_label || '';
                        }},
                        { data: 'notes', name: 'notes', orderable: false},
                        { data: 'notes_2', name: 'notes_2', orderable: false},
                        { data: 'user_name', name: 'user_name', orderable: false},
                        { data: 'edit', name: 'actions', orderable: false, searchable: false, className: 'rec-actions', render: function(data, type, row) {
                            if (type !== 'display') return '';
                            let html = '';
                            @can('update','App\Models\Record')
                                html += `<button class="btn btn-sm p-1 btn-icon text-primary edit_row" data-id="${data}" title="تعديل"><i class="fe fe-edit"></i></button>`;
                            @endcan
                            if (!isBranchUser) {
                                @can('archived','App\Models\Record')
                                    html += `<button class="btn btn-sm p-1 btn-icon text-primary archived_row" data-id="${row.archived || data}" title="أرشفة"><i class="fe fe-archive"></i></button>`;
                                @endcan
                                @can('delete','App\Models\Record')
                                    html += `<button class="btn btn-icon p-1 text-danger delete_row" data-id="${row.delete || data}" title="حذف"><i class="fe fe-trash"></i></button>`;
                                @endcan
                            }
                            return html;
                        }},
                    ]
                });                $(document).on('click', '.delete_row', function () {
                    const id = $(this).data('id'); // الحصول على ID الصف
                    if (confirm('هل أنت متأكد من حذف العنصر؟')) {
                        deleteRow(id); // استدعاء وظيفة الحذف
                    }
                });
                // وظيفة الحذف
                function deleteRow(id) {
                    $.ajax({
                        url: '{{ route("records.destroy", ":id") }}'.replace(':id', id),
                        method: 'DELETE',
                        data: {
                            "_token": "{{ csrf_token() }}",
                        },
                        success: function (response) {
                            alert('تم حذف العنصر بنجاح');
                            table.ajax.reload(null, false) // إعادة تحميل الجدول بعد الحذف
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            alert('هنالك خطاء في عملية الحذف.');
                        },
                    });
                }
                $(document).on('click', '.edit_row', function () {
                    const id = $(this).data('id'); // الحصول على ID الصف
                    editRecordForm(id); // استدعاء وظيفة الحذف
                });
                const record = {
                    id: '',
                    done: '',
                    date: '',
                    name: '',
                    financier_number: '',
                    address: '',
                    displacement_status: '',
                    income_status: '',
                    family_burden: '',
                    distributed: '',
                    branch_id: '',
                    eligible: '',
                    branch_notes: '',
                    management_status: '',
                    management_notes: '',
                    age: '',
                    patient_ID: '',
                    phone_number1: '',
                    phone_number2: '',
                    operation: '',
                    doctor: '',
                    amount: '',
                    doctor_share: '',
                    anesthesia: '',
                    anesthesiologists_share: '',
                    bed: '',
                    private: '',
                    notes: '',
                    notes_2: '',
                    user_name: '',
                    user_id: '',
                    user : '',
                }
                function editRecordForm(id) {
                    $.ajax({
                        url: '{{ route("records.edit", ":id") }}'.replace(':id', id),
                        method: 'GET',
                        success: function (response) {
                            record.id = response.id;
                            record.done = response.done;
                            record.date = response.date;
                            record.name = response.name;
                            record.financier_number = response.financier_number;
                            record.address = response.address;
                            record.displacement_status = response.displacement_status;
                            record.income_status = response.income_status;
                            record.family_burden = response.family_burden;
                            record.distributed = response.distributed === null ? '' : (response.distributed ? '1' : '0');
                            record.branch_id = response.branch_id === null ? '' : response.branch_id;
                            record.eligible = response.eligible === null ? '' : (response.eligible ? '1' : '0');
                            record.branch_notes = response.branch_notes === null ? '' : response.branch_notes;
                            record.management_status = response.management_status === null || response.management_status === undefined ? '' : response.management_status;
                            record.management_notes = response.management_notes === null || response.management_notes === undefined ? '' : response.management_notes;
                            record.age = response.age;
                            record.patient_ID = response.patient_ID;
                            record.phone_number1 = response.phone_number1;
                            record.phone_number2 = response.phone_number2;
                            record.operation = response.operation;
                            record.doctor = response.doctor;
                            record.amount = response.amount;
                            record.doctor_share = response.doctor_share;
                            record.anesthesia = response.anesthesia;
                            record.anesthesiologists_share = response.anesthesiologists_share;
                            record.bed = response.bed;
                            record.private = response.private;
                            record.notes = response.notes;
                            record.notes_2 = response.notes_2;
                            record.file = response.file;
                            record.user_name = response.user_name;
                            record.user_id = response.user_id;
                            record.user = response.user;
                            $.each(record, function(key, value) {
                                const input = $('#' + key); // البحث عن العنصر باستخدام id
                                if (input.length) { // التحقق إذا كان العنصر موجودًا
                                    input.val(value); // تعيين القيمة
                                }
                                if(key == 'done') {
                                    if(value == 1) {
                                        $('#done').prop('checked', true);
                                    }else{
                                        $('#done').prop('checked', false);
                                    }
                                    $('#done').val('');
                                }
                            });
                            $('#addRecord').remove();
                            $('#update').remove();
                            $('#recordFormTitle').text('تعديل السجل');
                            $('#btns_form').append(`
                                <button type="button" id="update" class="btn rec-btn rec-btn--primary"><i class="fe fe-check"></i> حفظ التعديلات</button>
                            `);
                            $('.editForm').css('display','block');
                            $('#editRecord').modal('show');
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            alert('هنالك خطأ في الإتصال بالسيرفر.');
                        },
                    })
                }
                $('.calculation').on('blur keypress', function (event) {
                    // تحقق إذا كان الحدث هو الضغط على مفتاح
                    if (event.type == 'keypress' && event.key != "Enter") {
                        return;
                    }
                    // استرجاع القيمة المدخلة
                    var input = $(this).val();
                    try {
                        // استخدام eval لحساب الناتج (مع الاحتياطات الأمنية)
                        var result = eval(input);
                        // عرض الناتج في الحقل
                        $(this).val(result);
                    } catch (e) {
                        // في حالة وجود خطأ (مثل إدخال غير صحيح)
                        alert('يرجى إدخال معادلة صحيحة!');
                    }
                });
                $('#amount').on('input', function() {
                    var amount = $(this).val();
                    if(amount != "" || amount != null){
                        $('#doctor_share').val((amount * 0.4).toFixed(2));
                        $('#private').val((amount * 0.05).toFixed(2));
                    }
                })
                $(document).on('click', '#update', function () {
                    let form = $('#editForm'); // اختر النموذج بشكل عام
                    let isValid = true;
                    isValid = form.validate().form(); // تحقق من صحة النموذج
                    if (isValid) {
                        $.each(record, function(key, value) {
                            const input = $('#' + key); // البحث عن العنصر باستخدام id
                            if(key == 'id'){
                                //
                            }else if(key == 'done'){
                                if(input.is(':checked')) {
                                    record[key] = 1;
                                }else{
                                    record[key] = 0;
                                }
                            }else{
                                record[key] = input.val();
                            }
                        });
                        let data = { ...record };
                        if (isBranchUser) {
                            data = { eligible: record.eligible, branch_notes: record.branch_notes, management_status: record.management_status, management_notes: record.management_notes };
                        } else {
                            delete data.eligible;
                            delete data.branch_notes;
                        }
                        if (!canManage) { delete data.management_status; delete data.management_notes; }
                        $.ajax({
                            url: "{{ route('records.update', ':id') }}".replace(':id', record.id),
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: data,
                            success: function (response) {
                                $('#editRecord').modal('hide');
                                table.ajax.reload(null, false)
                                alert('تم التعديل بنجاح');
                            },
                            error: function (xhr, status, error) {
                                console.error('AJAX error:', status, error);
                                alert('هنالك خطأ في إدخال الداتا يرجى التحقق منها.');
                            },
                        })
                        $('#alerts').slideUp();
                    } else {
                        // إذا كانت هناك أخطاء، لا تواصل
                        $('#alerts').slideDown();
                        $('#alerts').text("يرجى تصحيح الأخطاء في النموذج قبل المتابعة.");
                    }
                });
                $(document).on('click', '#createNew', function () {
                    $.ajax({
                        url: '{{ route("records.create") }}',
                        method: 'GET',
                        success: function (response) {
                            record.id = response.id;
                            record.done = response.done;
                            record.date = response.date;
                            record.name = response.name;
                            record.financier_number = response.financier_number;
                            record.address = response.address;
                            record.displacement_status = response.displacement_status;
                            record.income_status = response.income_status;
                            record.family_burden = response.family_burden;
                            record.distributed = response.distributed === null ? '' : (response.distributed ? '1' : '0');
                            record.branch_id = response.branch_id === null ? '' : response.branch_id;
                            record.eligible = response.eligible === null ? '' : (response.eligible ? '1' : '0');
                            record.branch_notes = response.branch_notes === null ? '' : response.branch_notes;
                            record.management_status = response.management_status === null || response.management_status === undefined ? '' : response.management_status;
                            record.management_notes = response.management_notes === null || response.management_notes === undefined ? '' : response.management_notes;
                            record.age = response.age;
                            record.patient_ID = response.patient_ID;
                            record.phone_number1 = response.phone_number1;
                            record.phone_number2 = response.phone_number2;
                            record.operation = response.operation;
                            record.doctor = response.doctor;
                            record.amount = response.amount;
                            record.doctor_share = response.doctor_share;
                            record.anesthesia = response.anesthesia;
                            record.anesthesiologists_share = response.anesthesiologists_share;
                            record.bed = response.bed;
                            record.private = response.private;
                            record.notes = response.notes;
                            record.notes_2 = response.notes_2;
                            record.user_name = response.user_name;
                            record.user_id = response.user_id;
                            record.user = response.user;
                            $.each(record, function(key, value) {
                                const input = $('#' + key); // البحث عن العنصر باستخدام id
                                if (input.length) { // التحقق إذا كان العنصر موجودًا
                                    input.val(value); // تعيين القيمة
                                }
                            });
                            $('#addRecord').remove();
                            $('#update').remove();
                            $('#recordFormTitle').text('إضافة سجل جديد');
                            $('#btns_form').append(`
                                <button type="button" id="addRecord" class="btn rec-btn rec-btn--primary"><i class="fe fe-plus"></i> إضافة السجل</button>
                            `);
                            $('.editForm').css('display','none');
                            $('#editRecord').modal('show');
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            alert('هنالك خطأ في الإتصال بالسيرفر.');
                        },
                    })
                });
                $(document).on('click', '#addRecord', function () {
                    const id = $(this).data('id'); // الحصول على ID الصف
                    createRecordForm(id);
                });
                function createRecordForm(id){
                    let form = $('#editForm'); // اختر النموذج بشكل عام
                    let isValid = true;
                    isValid = form.validate().form(); // تحقق من صحة النموذج
                    if (isValid) {
                        $.each(record, function(key, value) {
                            const input = $('#' + key); // البحث عن العنصر باستخدام id
                            if(key == 'id'){
                                //
                            }else if(key == 'done'){
                                if(input.is(':checked')) {
                                    record[key] = 1;
                                }else{
                                    record[key] = 0;
                                }
                            }else{
                                record[key] = input.val();
                            }
                        });
                        $.ajax({
                            url: "{{ route('records.store') }}",
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: record,
                            success: function (response) {
                                $('#editRecord').modal('hide');
                                table.ajax.reload(null, false)
                                alert('تم إضافة سجل جديد بنجاح');
                            },
                            error: function (xhr, status, error) {
                                console.error('AJAX error:', status, error);
                                alert('هنالك خطأ في إدخال الداتا يرجى التحقق منها.');
                            },
                        })
                        $('#alerts').slideUp();
                    } else {
                        // إذا كانت هناك أخطاء، لا تواصل
                        $('#alerts').slideDown();
                        $('#alerts').text("يرجى تصحيح الأخطاء في النموذج قبل المتابعة.");
                    }
                };
                $(document).on('click', '.archived_row', function () {
                    const id = $(this).data('id'); // الحصول على ID الصف
                    archivedRecordForm(id); // استدعاء وظيفة الحذف
                });
                function archivedRecordForm(id){
                    $.ajax({
                        url: "{{ route('records.archivedRow') }}",
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        data: { id: id , type : 'archived' },
                        success: function (response) {
                            table.ajax.reload(null, false)
                            alert('تم تحديث حالة السجل بنجاح');
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            alert('هنالك خطأ في الإتصال بالسيرفر.');
                        },
                    });
                }
            });
        </script>
        <script>
            $(document).ready(function() {
                document.documentElement.classList.toggle('rec-dark', curentTheme === 'dark');
                if (curentTheme == "light") {
                    $('#stickyTableLight').prop('disabled', false); // تشغيل النمط Light
                    $('#stickyTableDark').prop('disabled', true);  // تعطيل النمط Dark
                } else {
                    $('#stickyTableLight').prop('disabled', true);  // تعطيل النمط Light
                    $('#stickyTableDark').prop('disabled', false); // تشغيل النمط Dark
                }
                $(document).on('click', '#import_excel_btn', function() {
                    $('#editRecord').modal('hide');
                    $('#importExcel').modal('show');
                })
            });
        </script>
    @endpush
</x-front-layout>
