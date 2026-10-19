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
        <li class="nav-item mx-1 d-flex align-items-center"><button type="button" class="rec-tool rec-tool--primary" id="createNew"><i class="fe fe-plus"></i> إضافة سجل</button></li>
        @endif
        @endcan
        @canany(['export','exportexcel'],'App\\Models\Record')
        <li class="nav-item dropdown mx-1 d-flex align-items-center">
            <button class="rec-tool dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fe fe-download"></i> تصدير</button>
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
            <button type="button" class="rec-tool" id="filterBtnClear"><i class="fe fe-x"></i> مسح الفلاتر</button>
        </li>
        <li class="nav-item mx-1 d-flex align-items-center">
            <button type="button" class="rec-tool rec-tool--icon" id="refreshData" title="تحديث" aria-label="تحديث"><i class="fe fe-refresh-ccw"></i></button>
        </li>
    </x-slot:extra_nav>
    <div class="records-page">
    <div class="row">
        <div class="col-md-12"  style="padding: 0 2px;">
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
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-2" type="button" id="done_filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
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
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-3" type="button" id="date_filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
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
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>اسم المريض</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-4" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
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
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="4"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-4">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>رقم الممول</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-5" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
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
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="5" data-field="financier_number"> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>اسم الممول</th>
                                <th>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>العنوان</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-7" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="address_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="7" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="7" data-field="address">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="7"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-7">
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="7" data-field="address"> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class='d-flex align-items-center justify-content-between'>
                                        <span>حالة النزوح والسكن وتضرر البيت</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-8" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="btn-filter-8">
                                                    <select name="displacement_status_filter" class="form-control" style="width: 260px">
                                                        <option value=""></option>
                                                        @foreach (\App\Models\Record::DISPLACEMENT_STATUSES as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div>
                                                        <button class='btn btn-success text-white filter-apply-btn' data-target="8" data-field="displacement_status_filter">
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
                                        <span>مصادر الدخل والوضع المالي</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-9" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="btn-filter-9">
                                                    <select name="income_status_filter" class="form-control" style="width: 260px">
                                                        <option value=""></option>
                                                        @foreach (\App\Models\Record::INCOME_STATUSES as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div>
                                                        <button class='btn btn-success text-white filter-apply-btn' data-target="9" data-field="income_status_filter">
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
                                        <span>حجم الأسرة والحالات الخاصة</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-10" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="btn-filter-10">
                                                    <select name="family_burden_filter" class="form-control" style="width: 260px">
                                                        <option value=""></option>
                                                        @foreach (\App\Models\Record::FAMILY_BURDENS as $key => $label)
                                                            <option value="{{ $key }}">{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                                    <div>
                                                        <button class='btn btn-success text-white filter-apply-btn' data-target="10" data-field="family_burden_filter">
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
                                        <span>تم التوزيع</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-11" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="btn-filter-11">
                                                    <select name="distributed_filter" class="form-control" style="width: 260px">
                                                        <option value=""></option>
                                                        <option value="1">نعم</option>
                                                        <option value="0">لا</option>
                                                    </select>
                                                    <div>
                                                        <button class='btn btn-success text-white filter-apply-btn' data-target="11" data-field="distributed_filter">
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
                                        <span>العمر</span>
                                        <div class='filter-dropdown ml-4'>
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-12" type="button" id="date_filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
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
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>رقم الهوية</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-13" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="patient_ID_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="13" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="13" data-field="patient_ID_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="13"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-13">
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="13" data-field="patient_ID"> فارغ
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
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>العملية</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-16" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="operation_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="16" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="16" data-field="operation_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="16"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-16">
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="16" data-field="operation"> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>
                                    <div class="d-flex align-items-center justify-content-between">
                                        <span>الطبيب</span>
                                        <div class="filter-dropdown ml-4">
                                            <div class="dropdown">
                                                <button class="btn btn-secondary btn-filter" id="btn-filter-17" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                    <i class="fe fe-pocket text-white"></i>
                                                </button>
                                                <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" aria-labelledby="doctor_filter">
                                                    <!-- إضافة checkboxes بدلاً من select -->
                                                    <div class="searchable-checkbox">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <input type="search" class="form-control search-checkbox" data-index="17" placeholder="ابحث...">
                                                            <button class="btn btn-success text-white filter-apply-btn-checkbox" data-target="17" data-field="doctor_th">
                                                                <i class="fe fe-check"></i>
                                                            </button>
                                                        </div>
                                                        <div class="checkbox-list-box">
                                                            <label style="display: block;">
                                                                <input type="checkbox" value="all" class="all-checkbox" data-index="17"> الكل
                                                            </label>
                                                            <div class="checkbox-list checkbox-list-17">
                                                            </div>
                                                        </div>
                                                        <label>
                                                            <input type="checkbox" value="null" class="null-checkbox" data-index="17" data-field="doctor"> فارغ
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </th>
                                <th>التكلفة</th>
                                <th @cannot('financial','App\\Models\Record') class="d-none" @endcan>حصة الطبيب</th>
                                <th @cannot('financial','App\\Models\Record') class="d-none" @endcan>طبيب التخدير</th>
                                <th @cannot('financial','App\\Models\Record') class="d-none" @endcan>حصة التخدير</th>
                                <th @cannot('financial','App\\Models\Record') class="d-none" @endcan>المبيت</th>
                                <th @cannot('financial','App\\Models\Record') class="d-none" @endcan>خاص</th>
                                <th>ملاحظات</th>
                                <th>ملاحظات ثانوية</th>
                                <th>الفرع</th>
                                <th>يستحق</th>
                                <th>ملاحظات الفرع</th>
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
                                                        <button class='btn btn-success text-white filter-apply-btn' data-target="24" data-field="user_name">
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
                                <td class="text-white opacity-8 text-center" id="count_records"></td>
                                <td></td>
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
                                        columns: [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24], // تحديد الأعمدة التي سيتم تصديرها (يمكن تعديلها حسب الحاجة)
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
                        url: '{{ route("records.index") }}',
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
                            @can('update','App\\Models\Record')
                                let link = `<button class="btn btn-sm p-1 btn-icon text-primary edit_row"  data-id=":record"><i class="fe fe-edit"></i></button>`.replace(':record', data);
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
                        { data: 'address', name: 'address' , orderable: false},
                        { data: 'displacement_status', name: 'displacement_status', orderable: false, render: function(data, type, row) { return row.displacement_status_label || ''; }},
                        { data: 'income_status', name: 'income_status', orderable: false, render: function(data, type, row) { return row.income_status_label || ''; }},
                        { data: 'family_burden', name: 'family_burden', orderable: false, render: function(data, type, row) { return row.family_burden_label || ''; }},
                        { data: 'distributed', name: 'distributed', orderable: false, render: function(data, type, row) {
                            if (type === 'display') {
                                return row.distributed_label === 'نعم' ? '<span class="rec-chip rec-chip--yes">نعم</span>' : row.distributed_label === 'لا' ? '<span class="rec-chip rec-chip--no">لا</span>' : '';
                            }
                            return row.distributed_label || '';
                        }},
                        { data: 'age', name: 'age', orderable: false},
                        { data: 'patient_ID', name: 'patient_ID'  , orderable: false},
                        { data: 'phone_number1', name: 'phone_number1'  , orderable: false},
                        { data: 'phone_number2', name: 'phone_number2'  , orderable: false},
                        { data: 'operation', name: 'operation'  , orderable: false},
                        { data: 'doctor', name: 'doctor', orderable: false},
                        { data: 'amount', name: 'amount', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            return  formatNumber(data,2);
                        }},
                        { data: 'doctor_share', name: 'doctor_share', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'anesthesia', name: 'anesthesia', orderable: false},
                        { data: 'anesthesiologists_share', name: 'anesthesiologists_share', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'bed', name: 'bed', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'private', name: 'private', orderable: false, className: 'rec-num', render: function(data, type, row) {
                            @can('financial','App\\Models\Record')
                            return  formatNumber(data,2);
                            @else
                            return '';
                            @endcan
                        }},
                        { data: 'notes', name: 'notes'  , orderable: false},
                        { data: 'notes_2', name: 'notes_2'  , orderable: false},
                        { data: 'branch_name', name: 'branch_name', orderable: false},
                        { data: 'eligible_label', name: 'eligible_label', orderable: false},
                        { data: 'branch_notes', name: 'branch_notes', orderable: false},
                        { data: 'user_name', name: 'user_name'  , orderable: false},
                        { data: 'archived', name: 'archived', orderable: false, render: function(data, type, row) {
                            if (isBranchUser) return '';
                            @can('archived','App\\Models\Record')
                                let link = `<button class="btn btn-sm btn-icon text-primary archived_row"  data-id=":record"><i class="fe fe-archive"></i></button>`.replace(':record', data);
                                return link ;
                            @else
                            return '';
                            @endcan
                        }},
                        {data: 'delete', name: 'delete', orderable: false, searchable: false, render: function (data, type, row) {
                                if (isBranchUser) return '';
                                @can('delete','App\\Models\Record')
                                return `
                                    <button
                                        class="btn btn-icon p-1 text-danger delete_row"
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
                            $(this).find('td').eq(16).css('text-align', 'center');

                            @cannot('financial','App\\Models\Record')
                                $(this).find('td').eq(19).css('display', 'none');
                                $(this).find('td').eq(20).css('display', 'none');
                                $(this).find('td').eq(21).css('display', 'none');
                                $(this).find('td').eq(22).css('display', 'none');
                                $(this).find('td').eq(23).css('display', 'none');
                            @endcan
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
                            .column(18, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_doctor_share 14
                        var total_doctor_share_sum = api
                            .column(19, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_anesthesiologists_share 16
                        var total_anesthesiologists_share_sum = api
                            .column(21, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_bed 17
                        var total_bed_sum = api
                            .column(22, { page: 'current' }) // العمود الرابع
                            .data()
                            .reduce(function(a, b) {
                                return intVal(a) + intVal(b);
                            }, 0);
                        // total_private 18
                        var total_private_sum = api
                            .column(23, { page: 'current' }) // العمود الرابع
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
                $('#excel-export').on('click', function (e) {
                    let tableData = table.rows({ search: 'applied' }).data().toArray(); // الحصول على البيانات المعروضة فقط (المفلترة)
                    let jsonData = JSON.stringify(tableData); // تحويل البيانات إلى JSON
                    // تعيين القيمة في حقل <input> المخفي
                    $('input[name="data"]').val(jsonData);
                    $('#exportExcelForm').submit();
                });
                $(document).on('click', '#print-report', function () {
                    // بعد تحميل الجدول أو عند إجراء تغييرات عليه
                    let tableData = table.rows({ search: 'applied' }).data().toArray(); // الحصول على البيانات المعروضة فقط (المفلترة)

                    let jsonData = JSON.stringify(tableData); // تحويل البيانات إلى JSON
                    // تعيين القيمة في حقل <input> المخفي
                    $('input[name="data"]').val(jsonData);
                    $('#printReport').modal('show');
                });
                $('#records-table_filter').addClass('d-none');
                // جلب الداتا في checkbox
                function populateFilterOptions(columnIndex, container,name) {
                    const uniqueValues = [];
                    table.column(columnIndex, { search: 'applied' }).data().each(function (value) {
                        const stringValue = value ? String(value).trim() : ''; // تحويل القيمة إلى نص وإزالة الفراغات
                        if (stringValue && uniqueValues.indexOf(stringValue) === -1) {
                            uniqueValues.push(stringValue);
                        }
                    });
                    // ترتيب القيم أبجديًا
                    uniqueValues.sort();
                    // إضافة الخيارات إلى div
                    const checkboxList = $(container);
                    checkboxList.empty();
                    uniqueValues.forEach(value => {
                        checkboxList.append(`
                            <label style="display: block;">
                                <input type="checkbox" value="${value}" class="${name}-checkbox"> ${value}
                            </label>
                        `);
                    });
                }
                function isColumnFiltered(columnIndex) {
                    const filterValue = table.column(columnIndex).search();
                    return filterValue !== ""; // إذا لم يكن فارغًا، الفلترة مفعلة
                }
                // دالة لإعادة بناء الفلاتر بناءً على البيانات الحالية
                function rebuildFilters() {
                    isColumnFiltered(4) ? '' : populateFilterOptions(4, '.checkbox-list-4','name_th');
                    isColumnFiltered(5) ? '' : populateFilterOptions(5, '.checkbox-list-5','financier_th');
                    isColumnFiltered(7) ? '' : populateFilterOptions(7, '.checkbox-list-7','address');
                    isColumnFiltered(13) ? '' : populateFilterOptions(13, '.checkbox-list-13','patient_ID_th');
                    isColumnFiltered(16) ? '' : populateFilterOptions(16, '.checkbox-list-16','operation_th');
                    isColumnFiltered(17) ? '' : populateFilterOptions(17, '.checkbox-list-17','doctor_th');

                    for (let i = 1; i <= 24; i++) {
                        if (isColumnFiltered(i)) {
                            $('#btn-filter-' + i).removeClass('btn-secondary');
                            $('#btn-filter-' + i + ' i').removeClass('fe-pocket');
                            $('#btn-filter-' + i + ' i').addClass('fe-filter');
                            $('#btn-filter-' + i).addClass('btn-success');
                        }else{
                            $('#btn-filter-' + i + ' i').removeClass('fe-filter');
                            $('#btn-filter-' + i).removeClass('btn-success');
                            $('#btn-filter-' + i).addClass('btn-secondary');
                            $('#btn-filter-' + i + ' i').addClass('fe-pocket');
                        }
                    }
                }
                table.on('draw', function() {
                    rebuildFilters();
                });
                // تطبيق الفلترة عند الضغط على زر "check"
                let serchNull = false;
                $('.filter-apply-btn').on('click', function() {
                    let target = $(this).data('target');
                    let field = $(this).data('field');
                    var filterValue = $("input[name="+ field + "], select[name="+ field + "]").val();
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
                    $('.filterDropdownMenu input, .filterDropdownMenu select').val('');
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
                    address: '',
                    displacement_status: '',
                    income_status: '',
                    family_burden: '',
                    distributed: '',
                    branch_id: '',
                    eligible: '',
                    branch_notes: '',
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
                            data = { eligible: record.eligible, branch_notes: record.branch_notes };
                        } else {
                            delete data.eligible;
                            delete data.branch_notes;
                        }
                        $.ajax({
                            url: "{{ route('records.update', ':id') }}".replace(':id', record.id),
                            method: 'PUT',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            data: data,
                            success: function (response) {
                                $('#editRecord').modal('hide');
                                table.ajax.reload();
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
                                table.ajax.reload();
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
