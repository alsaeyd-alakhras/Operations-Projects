<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/records.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-pages.css') }}">
        <script>
            try { document.documentElement.classList.toggle('rec-dark', localStorage.getItem('mode') === 'dark'); } catch (e) {}
        </script>
    @endpush
    <div class="adm-page">
        <div class="adm-head">
            <h2 class="adm-title page-title">جدول الممولين @if($archiveEnable == true)<small>الأرشيف</small>@endif</h2>
            <div class="adm-actions">
                @if($archiveEnable != true)
                    <a class="rec-btn" href="{{ route('financiers.index', ['archive' => 1]) }}">
                        <i class="fe fe-archive"></i> الأرشيف
                    </a>
                    @can('print', 'App\\Models\Financier')
                    <form action="{{ route('financiers.print') }}" method="post" target="_blank">
                        @csrf
                        <input type="hidden" name="type" value="all">
                        <button type="submit" class="rec-btn"><i class="fe fe-printer"></i> طباعة pdf</button>
                    </form>
                    @endcan
                    @can('create', 'App\\Models\Financier')
                    <a class="rec-btn rec-btn--primary" href="{{ route('financiers.create') }}">
                        <i class="fe fe-plus"></i> إضافة ممول
                    </a>
                    @endcan
                @else
                    <a class="rec-btn" href="{{ route('financiers.index') }}">
                        <i class="fe fe-arrow-right"></i> العودة للممولين
                    </a>
                @endif
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-scroll">
                <table class="table adm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>رقم الممول</th>
                            <th>الاسم</th>
                            <th>المرحلة</th>
                            <th>اسم المدير</th>
                            <th>المبلغ ش</th>
                            <th>عدد الحالات</th>
                            <th class="adm-flag-cell">إنتهاء <br> المشروع</th>
                            <th class="adm-flag-cell">دفع <br> المشروع</th>
                            <th class="adm-flag-cell">توزيع <br> المشروع</th>
                            <th class="adm-flag-cell">أرشفة <br> المشروع</th>
                            <th class="adm-flag-cell">الحدث</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($financiers as $financier)
                        <tr>
                            <td class="adm-idx">{{ $loop->iteration }}</td>
                            <td class="adm-num">{{ $financier->financier_number }}</td>
                            <td class="adm-name">{{ $financier->name }}</td>
                            <td>{{ $financier->stage }}</td>
                            <td>{{ $financier->maneger_name }}</td>
                            <td class="adm-num">{{ $financier->amount_ils !== null ? number_format($financier->amount_ils, 2) : '' }}</td>
                            <td class="adm-num">{{ $financier->number_cases }}</td>
                            @foreach (['completion_project', 'push_project', 'project_distribution', 'project_archive'] as $flag)
                            <td class="adm-flag-cell">
                                @if($financier->{$flag} == 1)
                                    <span class="adm-flag adm-flag--on" title="نعم"><i class="fe fe-check"></i><span class="sr-only">نعم</span></span>
                                @else
                                    <span class="adm-flag" title="لا"><span class="sr-only">لا</span></span>
                                @endif
                            </td>
                            @endforeach
                            <td class="adm-act">
                                <div class="dropdown">
                                    <button class="adm-more" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="إجراءات الممول {{ $financier->name }}">
                                        <i class="fe fe-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right rec-menu">
                                        @can('print', 'App\\Models\Financier')
                                        <form action="{{ route('financiers.print') }}" method="post" target="_blank">
                                            @csrf
                                            <input type="hidden" name="type" value="single">
                                            <input type="hidden" name="id" value="{{ $financier->id }}">
                                            <button type="submit" class="dropdown-item"><i class="fe fe-printer"></i> طباعة</button>
                                        </form>
                                        @endcan
                                        @can('update', 'App\\Models\Financier')
                                        <a class="dropdown-item" href="{{ route('financiers.edit', $financier->id) }}"><i class="fe fe-edit"></i> تعديل</a>
                                        @endcan
                                        <form action="{{ route('financiers.archive') }}" method="post">
                                            @csrf
                                            <input type="hidden" name="id" value="{{ $financier->id }}">
                                            <button type="submit" class="dropdown-item">
                                                <i class="fe fe-archive"></i> {{ $archiveEnable == true ? 'إستعادة' : 'أرشفة' }}
                                            </button>
                                        </form>
                                        @can('delete', 'App\\Models\Financier')
                                        <form action="{{ route('financiers.destroy', $financier->id) }}" method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا الممول؟');">
                                            @csrf
                                            @method('delete')
                                            <button type="submit" class="dropdown-item dropdown-item--danger"><i class="fe fe-trash"></i> حذف</button>
                                        </form>
                                        @endcan
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="adm-empty">{{ $archiveEnable == true ? 'لا يوجد ممولون في الأرشيف' : 'لا يوجد ممولون بعد' }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-front-layout>
