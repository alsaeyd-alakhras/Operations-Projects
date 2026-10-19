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
            <h2 class="adm-title page-title">جدول الفروع</h2>
            <div class="adm-actions">
                @can('create', 'App\\Models\Branch')
                <a class="rec-btn rec-btn--primary" href="{{ route('branches.create') }}">
                    <i class="fe fe-plus"></i> إضافة فرع
                </a>
                @endcan
            </div>
        </div>

        <div class="adm-card">
            <div class="adm-scroll">
                <table class="table adm-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>اسم الفرع</th>
                            <th>عدد السجلات</th>
                            <th>عدد المستخدمين</th>
                            <th class="adm-flag-cell">الحدث</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($branches as $branch)
                        <tr>
                            <td class="adm-idx">{{ $loop->iteration }}</td>
                            <td class="adm-name">{{ $branch->name }}</td>
                            <td class="adm-num">{{ $branch->records_count }}</td>
                            <td class="adm-num">{{ $branch->users_count }}</td>
                            <td class="adm-act">
                                <div class="dropdown">
                                    <button class="adm-more" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="إجراءات الفرع {{ $branch->name }}">
                                        <i class="fe fe-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right rec-menu">
                                        @can('update', 'App\\Models\Branch')
                                        <a class="dropdown-item" href="{{ route('branches.edit', $branch->id) }}"><i class="fe fe-edit"></i> تعديل</a>
                                        @endcan
                                        @can('delete', 'App\\Models\Branch')
                                        <form action="{{ route('branches.destroy', $branch->id) }}" method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا الفرع؟');">
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
                            <td colspan="5" class="adm-empty">لا توجد فروع بعد</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-front-layout>
