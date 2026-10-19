<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    <div class="row justify-content-center">
        <div class="col-12">
            <div class="row align-items-center mb-2">
                <div class="col">
                    <h2 class="mb-2 page-title">جدول الفروع</h2>
                </div>
                <div class="col-auto">
                    @can('create', 'App\\Models\Branch')
                    <a class="btn btn-success" href="{{route('branches.create')}}">
                        <i class="fe fe-plus"></i>
                    </a>
                    @endcan
                </div>
            </div>
            <div class="row my-4">
                <div class="col-md-12">
                    <div class="card shadow">
                        <div class="card-body">
                            <table class="table table-hover table-bordered">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>اسم الفرع</th>
                                        <th>عدد السجلات</th>
                                        <th>عدد المستخدمين</th>
                                        <th>الحدث</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($branches as $branch)
                                    <tr>
                                        <td>{{$loop->iteration}}</td>
                                        <td>{{$branch->name}}</td>
                                        <td>{{$branch->records_count}}</td>
                                        <td>{{$branch->users_count}}</td>
                                        <td><button class="btn btn-sm dropdown-toggle more-horizontal" type="button"
                                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <span class="text-muted sr-only">Action</span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right">
                                                @can('update', 'App\\Models\Branch')
                                                <a class="dropdown-item" style="margin: 0.5rem -0.75rem; text-align: right;"
                                                    href="{{route('branches.edit',$branch->id)}}">تعديل</a>
                                                @endcan
                                                @can('delete', 'App\\Models\Branch')
                                                <form action="{{route('branches.destroy',$branch->id)}}" method="post">
                                                    @csrf
                                                    @method('delete')
                                                    <button type="submit" class="dropdown-item" style="margin: 0.5rem -0.75rem; text-align: right;">حذف</button>
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
                </div>
            </div>
        </div>
    </div>
</x-front-layout>
