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
            <div>
                <h2 class="adm-title page-title">ملفات الأعمدة</h2>
                <p class="adm-sub">مجموعات جاهزة من أعمدة جدول السجلات تُربط بالمستخدمين</p>
            </div>
            <div class="adm-actions">
                @can('create', 'App\\Models\ColumnProfile')
                <a class="rec-btn rec-btn--primary" href="{{ route('column-profiles.create') }}">
                    <i class="fe fe-plus"></i> إضافة ملف أعمدة
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
                            <th>اسم الملف</th>
                            <th>عدد الأعمدة</th>
                            <th>النوع</th>
                            <th>عدد المستخدمين</th>
                            <th class="adm-flag-cell">الحدث</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($profiles as $profile)
                        <tr>
                            <td class="adm-idx">{{ $loop->iteration }}</td>
                            <td class="adm-name">{{ $profile->name }}</td>
                            <td class="adm-num">{{ count($profile->validColumns()) }}</td>
                            <td>
                                @if ($profile->restricted)
                                    <span class="adm-status adm-status--on">مقيّد من السيرفر</span>
                                @else
                                    <span class="adm-status">افتراضي فقط</span>
                                @endif
                            </td>
                            <td class="adm-num">{{ $profile->users_count }}</td>
                            <td class="adm-act">
                                <div class="dropdown">
                                    <button class="adm-more" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="إجراءات الملف {{ $profile->name }}">
                                        <i class="fe fe-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right rec-menu">
                                        @can('update', 'App\\Models\ColumnProfile')
                                        <a class="dropdown-item" href="{{ route('column-profiles.edit', $profile->id) }}"><i class="fe fe-edit"></i> تعديل</a>
                                        @endcan
                                        @can('delete', 'App\\Models\ColumnProfile')
                                        <form action="{{ route('column-profiles.destroy', $profile->id) }}" method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا الملف؟ سيعود المستخدمون المرتبطون به ({{ $profile->users_count }}) للسلوك الافتراضي.');">
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
                            <td colspan="6" class="adm-empty">لا توجد ملفات أعمدة بعد</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-front-layout>
