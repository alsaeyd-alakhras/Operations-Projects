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
        <style>
            th{
                color: #fff !important;
            }
        </style>
    @endpush
    <x-slot:extra_nav>
        @can('export','App\\Models\Record')
        <li class="nav-item">
            <button type="button" class="btn btn-info" id="print-report" title="طباعة تقرير">
                <i class="fe fe-printer"></i> PDF
            </button>
        </li>
        @endcan
        @can('exportexcel','App\\Models\Record')
        <li class="nav-item">
            <button type="button" class="btn btn-success text-white" id="excel-export" title="تصدير excel">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512" width="16" height="16">
                    <path d="M64 0C28.7 0 0 28.7 0 64L0 448c0 35.3 28.7 64 64 64l256 0c35.3 0 64-28.7 64-64l0-288-128 0c-17.7 0-32-14.3-32-32L224 0 64 0zM256 0l0 128 128 0L256 0zM155.7 250.2L192 302.1l36.3-51.9c7.6-10.9 22.6-13.5 33.4-5.9s13.5 22.6 5.9 33.4L221.3 344l46.4 66.2c7.6 10.9 5 25.8-5.9 33.4s-25.8 5-33.4-5.9L192 385.8l-36.3 51.9c-7.6 10.9-22.6 13.5-33.4 5.9s-13.5-22.6-5.9-33.4L162.7 344l-46.4-66.2c-7.6-10.9-5-25.8 5.9-33.4s25.8-5 33.4 5.9z"/>
                </svg>
            </button>
        </li>
        @endcan
        <li class="nav-item dropdown d-flex align-items-center justify-content-center mx-2">
            <a class="nav-link dropdown-toggle text-white pr-0" href="#" id="navbarDropdownMenuLink"
                role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="avatar avatar-sm mt-2">
                    <i class="fe fe-filter fe-16"></i>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                <button class="btn btn-nav" id="filterBtn">تصفية</button>
                <button class="btn btn-nav" id="filterBtnClear">إزالة التصفية</button>
            </div>
        </li>
        <li class="nav-item d-flex align-items-center justify-content-center mx-2">
            <button type="button" class="btn" id="refreshData"><span class="fe fe-refresh-ccw fe-16 text-white"></span></button>
        </li>
    </x-slot:extra_nav>
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body table-container p-0">
                    <table id="records-table" class="table table-striped table-bordered table-hover sticky" style="width:100%; height: calc(100vh - 100px);">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="text-white text-center">#</th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>تمت</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn" style="padding: 0; margin: 0; border: none;" type="button" id="done_filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="done_filter">
                                                    <input type="text" name="done" class="form-control mr-2  py-0 px-2" list="done_list" style="width: 200px"/>
                                                    <datalist id="done_list">
                                                        <option value="1">نعم</option>
                                                        <option value="0">لا</option>
                                                    </datalist>
                                                    <div>
                                                        <button class='btn btn-success text-white filter-apply-btn' data-target="2" data-field="done">
                                                            <i class='fe fe-check'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>التاريخ</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn" style="padding: 0; margin: 0; border: none;" type="button" id="date_filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="date_filter">
                                                    <div>
                                                        <input type="date" id="from_date" name="from_date_record" class="form-control mr-2" style="width: 200px"/>
                                                        <input type="date" id="to_date" value="{{Carbon\Carbon::now()->format('Y-m-d')}}" name="to_date_record" class="form-control mr-2 mt-2" style="width: 200px"/>
                                                    </div>
                                                    <div>
                                                        <button id="filter-date-btn" class='btn btn-success text-white filter-apply-btn-data' data-target="2" data-field="date_name">
                                                            <i class='fe fe-check'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th class="sticky" style="right: 0px;">
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>اسم المريض</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="name_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="4" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="4" data-field="name_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <div class="checkbox-list checkbox-list-4">
                                                                @foreach ($names as $name)
                                                                    <label style="display: block;">
                                                                        <input type="checkbox" value="{{ $name }}" class="name_th-checkbox"> {{ $name }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>رقم الممول</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="financier_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="5" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="5" data-field="financier_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="5"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-5">
                                                                @foreach ($financiers as $financier)
                                                                    <label style="display: block;">
                                                                        <input type="checkbox" value="{{ $financier }}" class="financier_th-checkbox"> {{ $financier }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="5" data-field="financier_number" disabled> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>اسم الممول</th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>العمر</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn" style="padding: 0; margin: 0; border: none;" type="button" id="date_filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="date_filter">
                                                    <div>
                                                        <input type="number" id="from_age" name="from_age_record" class="form-control mr-2" style="width: 200px"/>
                                                        <input type="number" id="to_age" name="to_age_record" class="form-control mr-2 mt-2" style="width: 200px"/>
                                                    </div>
                                                    <div>
                                                        <button id="filter-age-btn" class='btn btn-success text-white filter-apply-btn-data'>
                                                            <i class='fe fe-check'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>رقم الهوية</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="patient_ID_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="8" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="8" data-field="patient_ID_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <div class="checkbox-list checkbox-list-8">
                                                                @foreach ($pat_ids as $pat_id)
                                                                    <label style="display: block;">
                                                                        <input type="checkbox" value="{{ $pat_id }}" class="patient_ID_th-checkbox"> {{ $pat_id }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="8" data-field="patient_ID" disabled> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>جوال 1</th>
                                <th>جوال 2</th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>العملية</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="operation_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="11" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="11" data-field="operation_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="11"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-11">
                                                                @foreach ($operations as $operation)
                                                                    <label style="display: block;">
                                                                        <input type="checkbox" value="{{ $operation }}" class="operation_th-checkbox"> {{ $operation }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="11" data-field="operation" disabled> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>الطبيب</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="doctor_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="12" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="12" data-field="doctor_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="12"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-12">
                                                                @foreach ($doctors as $doctor)
                                                                    <label style="display: block;">
                                                                        <input type="checkbox" value="{{ $doctor }}" class="doctor_th-checkbox"> {{ $doctor }}
                                                                    </label>
                                                                @endforeach
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="12" data-field="doctor" disabled> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>التكلفة</th>
                                @can('financial','App\\Models\Record')
                                    <th>حصة الطبيب</th>
                                    <th>طبيب التخدير</th>
                                    <th>حصة التخدير</th>
                                    <th>المبيت</th>
                                    <th>خاص</th>
                                @else
                                    <th></th>
                                    <th>طبيب التخدير</th>
                                    <th></th>
                                    <th></th>
                                    <th></th>
                                @endcan
                                <th>ملاحظات</th>
                                <th>ملاحظات ثانوية</th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>المستخدم</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn" style="padding: 0; margin: 0; border: none;" type="button" id="user_name_filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-filter text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="user_name_filter">
                                                    <input type="text" name="user_name" class="form-control mr-2  py-0 px-2" list="user_names_list" style="width: 200px"/>
                                                    <datalist id="user_names_list">
                                                        @foreach ($user_names as $user_name)
                                                            <option value="{{$user_name}}">
                                                        @endforeach
                                                    </datalist>
                                                    <div>
                                                        <button class='btn btn-success text-white filter-apply-btn' data-target="19" data-field="user_name">
                                                            <i class='fe fe-check'></i>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td class="text-white opacity-7 text-center" id="count_records"></td>
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
                                {{-- 13 --}}
                                <td class='text-white' id="total_amount"></td>
                                @can('financial','App\\Models\Record')
                                    <td class='text-white' id="total_doctor_share"></td>
                                    <td></td>
                                    <td class='text-white' id="total_anesthesiologists_share"></td>
                                    <td class='text-white' id="total_bed"></td>
                                    <td class='text-white' id="total_private"></td>
                                @else
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                @endcan
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <input type="hidden" name="fieldNull" id="fieldNull">
    <!-- Fullscreen modal -->
    <div class="modal fade modal-full" id="editRecord" tabindex="-1" role="dialog" aria-labelledby="mySmallModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
        <button aria-label="" type="button" class="close px-2" data-dismiss="modal" aria-hidden="true">
            <span aria-hidden="true">×</span>
        </button>
        <div class="modal-dialog modal-dialog-centered w-100" role="document" style="max-width: 95%;">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <form id="editForm" enctype="multipart/form-data">
                        @include('dashboard.records.editModal')
                    </form>
                </div>
            </div>
        </div>
    </div> <!-- small modal -->

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


    @push('scripts')
        <!-- DataTables JS -->
        <script src="{{asset('js/datatable/jquery.dataTables.min.js')}}"></script>
        <script src="{{asset('js/datatable/dataTables.js')}}"></script>
        <script src="{{asset('js/datatable/dataTables.buttons.js')}}"></script>
        <script src="{{asset('js/datatable/buttons.dataTables.js')}}"></script>
        <script src="{{asset('js/datatable/jszip.min.js')}}"></script>
        <script src="{{asset('js/datatable/pdfmake.min.js')}}"></script>
        <script src="{{asset('js/datatable/vfs_fonts.js')}}"></script>
        <script src="{{asset('js/datatable/buttons.html5.min.js')}}"></script>
        <script src="{{asset('js/datatable/buttons.print.min.js')}}"></script>

        <script src="{{asset('js/jquery.validate.min.js')}}"></script>
        <script type="text/javascript">
            $(document).ready(function() {
                $.validator.messages.required = "هذا الحقل مطلوب";
                $("#editForm").validate({
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
                let table = $('#records-table').DataTable({
                    processing: true,
                    serverSide: true,
                    responsive: true,
                    paging: false,              // تعطيل الترقيم
                    searching: true,            // الإبقاء على البحث إذا كنت تريده
                    info: false,                // تعطيل النص السفلي الذي يوضح عدد السجلات
                    lengthChange: false,        // تعطيل قائمة تغيير عدد المدخلات
                    layout: {
                        topStart: {
                            buttons: [
                                {
                                    extend: 'excelHtml5',
                                    text: 'تصدير Excel',
                                    title: 'التقارير', // تخصيص العنوان عند التصدير
                                    className: 'd-none', // إخفاء الزر الأصلي
                                    exportOptions: {
                                        columns: [1, 2, 3, 4, 5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20], // تحديد الأعمدة التي سيتم تصديرها (يمكن تعديلها حسب الحاجة)
                                        modifier: {
                                            search: 'applied', // تصدير البيانات المفلترة فقط
                                            order: 'applied',  // تصدير البيانات مع الترتيب الحالي
                                            page: 'all'        // تصدير جميع الصفحات المفلترة
                                        }
                                    }
                                },
                            ]
                        }
                    },
                    "language": {
                        "url": "{{ asset('files/Arabic.json')}}"
                    },
                    ajax: {
                        url: '{{ route("records.archived") }}',
                        data: function (d) {
                            // إضافة تواريخ التصفية إلى الطلب المرسل
                            d.from_date = $('#from_date').val();
                            d.to_date = $('#to_date').val();
                            d.from_age = $('#from_age').val();
                            d.to_age = $('#to_age').val();
                            d.fieldNull = $('#fieldNull').val();
                        },
                        error: function(xhr, status, error) {
                            console.error('AJAX error:', status, error);
                        }
                    },
                    columns: [
                        { data: 'edit', name: 'edit', orderable: false, searchable: false, render: function(data, type, row) {
                            @can('create','App\\Models\Record')
                                let link = `<button class="btn btn-sm btn-icon text-primary edit_row"  data-id=":record"><i class="fe fe-edit"></i></button>`.replace(':record', data);
                                return link ;
                            @else
                                return '';
                            @endcan
                        }},
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false}, // عمود الترقيم التلقائي
                        { data: 'done', name: 'done' , orderable: false, render: function(data, type, row) {
                            let link = `<input type="checkbox" ${data == 1 ? 'checked' : ''} disabled>`;
                            return link ;
                        }},
                        { data: 'date', name: 'date'  , orderable: false},
                        { data: 'name', name: 'name' , orderable: false, class: 'sticky' },
                        { data: 'financier_number', name: 'financier_number' , orderable: false},
                        { data: 'financier', name: 'financier' , orderable: false},
                        { data: 'age', name: 'age', orderable: false},
                        { data: 'patient_ID', name: 'patient_ID'  , orderable: false},
                        { data: 'phone_number1', name: 'phone_number1'  , orderable: false},
                        { data: 'phone_number2', name: 'phone_number2'  , orderable: false},
                        { data: 'operation', name: 'operation'  , orderable: false},
                        { data: 'doctor', name: 'doctor', orderable: false},
                        { data: 'amount', name: 'amount', orderable: false, render: function(data, type, row) {
                            return  formatNumber(data,2);
                        }},
                        @can('financial','App\\Models\Record')
                        { data: 'doctor_share', name: 'doctor_share', orderable: false, render: function(data, type, row) {
                            return  formatNumber(data,2);
                        }},
                        { data: 'anesthesia', name: 'anesthesia', orderable: false},
                        { data: 'anesthesiologists_share', name: 'anesthesiologists_share', orderable: false, render: function(data, type, row) {
                            return  formatNumber(data,2);
                        }},
                        { data: 'bed', name: 'bed', orderable: false, render: function(data, type, row) {
                            return  formatNumber(data,2);
                        }},
                        { data: 'private', name: 'private', orderable: false, render: function(data, type, row) {
                            return  formatNumber(data,2);
                        }},
                        @endcan
                        { data: 'notes', name: 'notes'  , orderable: false},
                        { data: 'notes_2', name: 'notes_2'  , orderable: false},
                        { data: 'user_name', name: 'user_name'  , orderable: false},
                        { data: 'archived', name: 'archived', orderable: false, render: function(data, type, row) {
                            @can('archived','App\\Models\Record')
                                let link = `<button class="btn btn-sm btn-icon text-primary archived_row"  data-id=":record"><i class="fe fe-chevrons-up"></i></button>`.replace(':record', data);
                                return link ;
                            @else
                            return '';
                            @endcan
                        }},
                        {data: 'delete', name: 'delete', orderable: false, searchable: false, render: function (data, type, row) {
                                @can('delete','App\\Models\Record')
                                return `
                                    <button
                                        class="btn btn-icon text-danger delete_row"
                                        data-id="${data}">
                                        <i class="fe fe-trash"></i>
                                    </button>`;
                                @else
                                return '';
                                @endcan
                        }},
                    ],
                    columnDefs: [
                        { targets: 1, searchable: false, orderable: false } // تعطيل الفرز والبحث على عمود الترقيم
                    ],
                    drawCallback: function(settings) {
                        // تطبيق التنسيق على خلايا العمود المحدد
                        $('#records-table tbody tr').each(function() {
                            $(this).find('td').eq(1).css('text-align', 'center');
                            $(this).find('td').eq(2).css('text-align', 'center');
                            $(this).find('td').eq(4).css('right', '0px');
                            $(this).find('td').eq(5).css('text-align', 'center');
                            $(this).find('td').eq(6).css('text-align', 'center');
                            $(this).find('td').eq(12).css('text-align', 'center');
                        });
                    },
                    footerCallback: function(row, data, start, end, display) {
                        var api = this.api();
                        // تحويل القيم النصية إلى أرقام
                        var intVal = function(i) {
                            return typeof i === 'string' ?
                                parseFloat(i.replace(/[\$,]/g, '')) :
                                typeof i === 'number' ? i : 0;
                        };
                        // 1. حساب عدد الأسطر في الصفحة الحالية
                        // count_records 1
                        var rowCount = display.length;
                        // total_amount 13
                        var total_amount_sum = api
                            .column(13, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_doctor_share 14
                        var total_doctor_share_sum = api
                            .column(14, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_anesthesiologists_share 16
                        var total_anesthesiologists_share_sum = api
                            .column(16, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_bed 17
                        var total_bed_sum = api
                            .column(17, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_private 18
                        var total_private_sum = api
                            .column(18, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);


                        // // 4. عرض النتائج في `tfoot`
                        $('#count_records').html(formatNumber(rowCount));
                        $('#total_amount').html(formatNumber(total_amount_sum,2));
                        $('#total_doctor_share').html(formatNumber(total_doctor_share_sum,2));
                        $('#total_anesthesiologists_share').html(formatNumber(total_anesthesiologists_share_sum,2));
                        $('#total_bed').html(formatNumber(total_bed_sum,2));
                        $('#total_private').html(formatNumber(total_private_sum,2));
                        // $('#records-table_filter').addClass('d-none');
                    }
                });
                // عندما يتم رسم الجدول (بعد تحميل البيانات أو تحديثها)
                table.on('draw', function() {
                    // التمرير إلى آخر سطر في الجدول
                    let tableContainer = $('#records-table');
                    tableContainer.scrollTop(tableContainer[0].scrollHeight);
                });
                // نسخ وظيفة الزر إلى الزر المخصص
                $('#excel-export').on('click', function () {
                    table.button('.buttons-excel').trigger(); // استدعاء وظيفة الزر الأصلي
                });
                $(document).on('click', '#print-report', function () {
                    // بعد تحميل الجدول أو عند إجراء تغييرات عليه
                    let tableData = table.rows({ search: 'applied' }).data().toArray(); // الحصول على البيانات المعروضة فقط (المفلترة)

                    let jsonData = JSON.stringify(tableData); // تحويل البيانات إلى JSON
                    console.log(tableData,jsonData);
                    // تعيين القيمة في حقل <input> المخفي
                    $('input[name="data"]').val(jsonData);
                    $('#printReport').modal('show');
                });
                $('#records-table_filter').addClass('d-none');
                // تطبيق الفلترة عند الضغط على زر "check"
                // تطبيق الفلترة عند الضغط على زر "check"
                let serchNull = false;
                $('.filter-apply-btn').on('click', function() {
                    let target = $(this).data('target');
                    let field = $(this).data('field');
                    var filterValue = $("input[name="+ field + "]").val();
                    console.log(filterValue,'2');
                    if(filterValue == '000'){
                        const fieldNull = $('#fieldNull').val(field);
                        serchNull = true;
                        table.ajax.reload(); // إعادة تحميل الجدول مع التواريخ المحدثة
                    }else{
                        if(serchNull == true){
                            serchNull = false;
                            const fieldNull = $('#fieldNull').val(null);
                            table.ajax.reload();
                        }
                        table.column(target).search(filterValue).draw();
                    }
                });
                // إضافة وظيفة البحث في الـ checkboxes
                // منع إغلاق dropdown عند النقر على input أو label
                $('th  .dropdown-menu .checkbox-list-box').on('click', function (e) {
                    e.stopPropagation(); // منع انتشار الحدث
                });
                // البحث داخل الـ checkboxes
                $('.search-checkbox').on('input', function() {
                    let searchValue = $(this).val().toLowerCase();
                    let tdIndex = $(this).data('index');
                    $('.checkbox-list-' + tdIndex + ' label').each(function() {
                        let labelText = $(this).text().toLowerCase();  // النص داخل الـ label
                        let checkbox = $(this).find('input');  // الـ checkbox داخل الـ label

                        if (labelText.indexOf(searchValue) !== -1) {
                            $(this).show();
                        } else {
                            $(this).hide();
                            if (checkbox.prop('checked')) {
                                checkbox.prop('checked', false);  // إذا كان الـ checkbox محددًا، قم بإلغاء تحديده
                            }
                        }
                    });
                });
                $('.all-checkbox').on('change', function() {
                    let index = $(this).data('index'); // الحصول على الـ index من الـ data-index

                    // التحقق من حالة الـ checkbox "الكل"
                    if ($(this).prop('checked')) {
                        // إذا كانت الـ checkbox "الكل" محددة، تحديد جميع الـ checkboxes الظاهرة فقط
                        $('.checkbox-list-' + index + ' input[type="checkbox"]:visible').prop('checked', true);
                    } else {
                        // إذا كانت الـ checkbox "الكل" غير محددة، إلغاء تحديد جميع الـ checkboxes الظاهرة فقط
                        $('.checkbox-list-' + index + ' input[type="checkbox"]:visible').prop('checked', false);
                    }
                });
                $('.filter-apply-btn-checkbox').on('click', function() {
                    let target = $(this).data('target'); // استرجاع الهدف (العمود)
                    let field = $(this).data('field'); // استرجاع الحقل (اسم المشروع أو أي حقل آخر)
                    if(serchNull == true){
                        serchNull = false;
                        const fieldNull = $('#fieldNull').val(null);
                        table.ajax.reload();
                    }
                    // الحصول على القيم المحددة من الـ checkboxes
                    var filterValues = [];
                    // نستخدم الكلاس المناسب بناءً على الحقل (هنا مشروع)
                    $('.' + field + '-checkbox:checked').each(function() {
                        filterValues.push($(this).val()); // إضافة القيمة المحددة
                    });
                    // إذا كانت هناك قيم محددة، نستخدمها في الفلترة
                    if (filterValues.length > 0) {
                        // دمج القيم باستخدام OR (|) كما هو متوقع في البحث
                        var searchExpression = filterValues.join('|');
                        // تطبيق الفلترة على العمود باستخدام القيم المحددة
                        table.column(target).search(searchExpression, true, false).draw();
                    } else {
                        // إذا لم تكن هناك قيم محددة، نعرض جميع البيانات
                        table.column(target).search('').draw();
                    }
                });
                $('.null-checkbox').on('click', function() {
                    let field = $(this).data('field');
                    serchNull == true ? serchNull = false : serchNull = true;
                    if(serchNull == true){
                        const fieldNull = $('#fieldNull').val(field);
                    }else{
                        const fieldNull = $('#fieldNull').val(null);
                    }
                    table.ajax.reload(); // إعادة تحميل الجدول مع التواريخ المحدثة
                });
                // تطبيق التصفية عند النقر على زر "Apply"
                $('#filter-date-btn').on('click', function () {
                    const fromDate = $('#from_date').val();
                    const toDate = $('#to_date').val();
                    table.ajax.reload(); // إعادة تحميل الجدول مع التواريخ المحدثة
                });
                $('#filter-age-btn').on('click', function () {
                    const fromAge = $('#from_age').val();
                    const toAge = $('#to_age').val();
                    table.ajax.reload(); // إعادة تحميل الجدول مع التواريخ المحدثة
                });
                // تفويض حدث الحذف على الأزرار الديناميكية
                $(document).on('click', '.delete_row', function () {
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
                            table.ajax.reload(); // إعادة تحميل الجدول بعد الحذف
                        },
                        error: function (xhr, status, error) {
                            console.error('AJAX error:', status, error);
                            alert('هنالك خطاء في عملية الحذف.');
                        },
                    });
                }
                $(document).on('click', '#refreshData', function() {
                    table.ajax.reload();
                });
                $(document).on('click', '#filterBtnClear', function() {
                    $('.filter-dropdown').slideUp();
                    $('#filterBtn').text('تصفية');
                    $('.filterDropdownMenu input').val('');
                    $('th input[type="checkbox"]').prop('checked', false);
                    table.columns().search('').draw(); // إعادة رسم الجدول بدون فلاتر
                });
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
                            $('#btns_form').append(`
                                <button type="button" id="update" class="btn btn-primary mx-2">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                    تعديل
                                </button>
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
                        $.ajax({
                            url: "{{ route('records.update', ':id') }}".replace(':id', record.id),
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: record,
                            success: function (response) {
                                $('#editRecord').modal('hide');
                                table.ajax.reload();
                                alert('تم التعديل بنجاح');
                            },
                            error: function (xhr, status, error) {
                                console.error('AJAX error:', status, error);
                                alert('هنالك خطأ في الإتصال بالسيرفر.');
                            },
                        })
                        $('#alerts').slideUp();
                    } else {
                        // إذا كانت هناك أخطاء، لا تواصل
                        $('#alerts').slideDown();
                        $('#alerts').text("يرجى تصحيح الأخطاء في النموذج قبل المتابعة.");
                    }
                });
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
                        data: { id: id , type : 'restore' },
                        success: function (response) {
                            table.ajax.reload();
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
                $(document).on('click', '#filterBtn', function() {
                    let text = $(this).text();
                    if (text != 'تصفية') {
                        $(this).text('تصفية');
                    }else{
                        $(this).text('إخفاء التصفية');
                    }
                    $('.filter-dropdown').slideToggle();
                });
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
