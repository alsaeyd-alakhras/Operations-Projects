<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="mb-2 page-title">جدول الممولين - {{ $archiveEnable == true ? 'الأرشيف' : '' }}</h2>
                </div>
                <div class="col-auto d-flex justify-content-end align-items-center">
                    @if($archiveEnable != true)
                    <a class="btn btn-info mx-2" href="{{route('financiers.index',['archive' => 1])}}">
                        الأرشيف
                    </a>
                    @can('create', 'App\\Models\Financier')
                    <a class="btn btn-success mx-2" href="{{route('financiers.create')}}">
                        <i class="fe fe-plus"></i>
                    </a>
                    @endcan
                    @can('print', 'App\\Models\Financier')
                    <form action="{{route('financiers.print')}}" method="post" target="_blank">
                        @csrf
                        <input type="hidden" name="type" value="all">
                        <button type="submit" class="btn btn-info">طباعة pdf</button>
                    </form>
                    @endcan
                    @endif
                </div>
            </div>
            <div class="row my-4">
                <!-- Small table -->
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <!-- table -->
                            <style>
                                th,td{
                                    color: #000 !important;
                                    font-size: 16px !important;
                                }
                            </style>
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>رقم الممول</th>
                                        <th>الاسم</th>
                                        <th>المرحلة</th>
                                        <th>اسم المدير</th>
                                        <th>الملبع ش</th>
                                        <th>عدد الحالات</th>
                                        <th class="text-center">إنتهاء <br> المشروع</th>
                                        <th class="text-center">دفع <br> المشروع</th>
                                        <th class="text-center">توزيع <br> المشروع</th>
                                        <th class="text-center">أرشفة <br> المشروع</th>
                                        <th>الحدث</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($financiers as $financier)
                                    <tr >
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$financier->financier_number}}</td>
                                        <td>{{$financier->name}}</td>
                                        <td>{{$financier->stage}}</td>
                                        <td>{{$financier->manager_name}}</td>
                                        <td>{{$financier->amount_ils}}</td>
                                        <td>{{$financier->number_cases}}</td>
                                        <td class="text-center">
                                            <div class="checkbox-wrapper-46">
                                                <input type="checkbox" id="completion_project" name="completion_project" class="inp-cbx" @checked($financier->completion_project == 1) readonly />
                                                <label class="cbx">
                                                    <span>
                                                        <svg viewBox="0 0 12 10" height="10px" width="12px">
                                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                        </svg>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="checkbox-wrapper-46">
                                                <input type="checkbox" id="push_project" name="push_project" class="inp-cbx" @checked($financier->push_project == 1) readonly />
                                                <label class="cbx">
                                                    <span>
                                                        <svg viewBox="0 0 12 10" height="10px" width="12px">
                                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                        </svg>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="checkbox-wrapper-46">
                                                <input type="checkbox" id="project_distribution" name="project_distribution" class="inp-cbx" @checked($financier->project_distribution == 1) readonly />
                                                <label class="cbx">
                                                    <span>
                                                        <svg viewBox="0 0 12 10" height="10px" width="12px">
                                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                        </svg>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="checkbox-wrapper-46">
                                                <input type="checkbox" id="project_archive" name="project_archive" class="inp-cbx" @checked($financier->project_archive == 1) readonly />
                                                <label class="cbx">
                                                    <span>
                                                        <svg viewBox="0 0 12 10" height="10px" width="12px">
                                                            <polyline points="1.5 6 4.5 9 10.5 1"></polyline>
                                                        </svg>
                                                    </span>
                                                </label>
                                            </div>
                                        </td>
                                        <td><button class="btn btn-sm dropdown-toggle more-horizontal" type="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="text-muted sr-only">Action</span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @can('print', 'App\\Models\Financier')
                                                <form action="{{route('financiers.print')}}" method="post" target="_blank">
                                                    @csrf
                                                    <input type="hidden" name="type" value="single">
                                                    <input type="hidden" name="id" value="{{$financier->id}}">
                                                    <button type="submit" class="dropdown-item" style="margin: 0.5rem -0.75rem; text-align: right;"
                                                    href="#">طباعة</button>
                                                </form>
                                                @endcan
                                                @can('update', 'App\\Models\Financier')
                                                <a class="dropdown-item" style="margin: 0.5rem -0.75rem; text-align: right;"
                                                    href="{{route('financiers.edit',$financier->id)}}">تعديل</a>
                                                @endcan
                                                <form action="{{route('financiers.archive')}}" method="post">
                                                    @csrf
                                                    <input type="hidden" name="id" value="{{$financier->id}}">
                                                    <button type="submit" class="dropdown-item" style="margin: 0.5rem -0.75rem; text-align: right;" href="#">
                                                        {{ $archiveEnable == true ? 'إستعادة' : 'أرشفة' }}
                                                    </button>
                                                </form>
                                                @can('delete', 'App\\Models\Financier')
                                                <form action="{{route('financiers.destroy',$financier->id)}}" method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="dropdown-item" style="margin: 0.5rem -0.75rem; text-align: right;"
                                                    href="#">حذف</button>
                                                </form>
                                                @endcan
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div> <!-- simple table -->
            </div> <!-- end section -->
        </div> <!-- .col-12 -->
    </div> <!-- .row -->
</x-front-layout>
