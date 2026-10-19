<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/records.css') }}">
        <link rel="stylesheet" href="{{ asset('css/admin-pages.css') }}">
        <script>
            try { document.documentElement.classList.toggle('rec-dark', localStorage.getItem('mode') === 'dark'); } catch (e) {}
        </script>
        <style>
            #user-1 { display: none; }
        </style>
    @endpush
    <div class="adm-page">
        <div class="adm-head">
            <div>
                <h2 class="adm-title page-title">جدول المستخدمين</h2>
                <p class="adm-sub">هنا يتم عرض بيانات المستخدمين ويمكنك التحكم في صلاحياتهم كاملة</p>
            </div>
            <div class="adm-actions">
                @can('create', 'App\\Models\User')
                <a class="rec-btn rec-btn--primary" href="{{ route('users.create') }}">
                    <i class="fe fe-plus"></i> إضافة مستخدم
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
                            <th>حالة النشاط</th>
                            <th>الاسم</th>
                            <th>اسم المستخدم</th>
                            <th>البريد الالكتروني</th>
                            <th>الفرع</th>
                            <th>أخر موعد تواجد</th>
                            <th class="adm-flag-cell">الحدث</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr id="user-{{ $user->id }}">
                            <td class="adm-idx">{{ $loop->iteration - 1 }}</td>
                            <td>
                                @if ($user->last_activity >= now()->subMinutes(5))
                                    <span class="adm-status adm-status--on">متصل</span>
                                @else
                                    <span class="adm-status">غير متصل</span>
                                @endif
                            </td>
                            <td class="adm-name">{{ $user->name }}</td>
                            <td>{{ $user->username }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->branch?->name }}</td>
                            <td class="adm-num">{{ $user->last_activity }}</td>
                            <td class="adm-act">
                                <div class="dropdown">
                                    <button class="adm-more" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" aria-label="إجراءات المستخدم {{ $user->name }}">
                                        <i class="fe fe-more-horizontal"></i>
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right rec-menu">
                                        @can('update', 'App\\Models\User')
                                        <a class="dropdown-item" href="{{ route('users.edit', $user->id) }}"><i class="fe fe-edit"></i> تعديل</a>
                                        @endcan
                                        @can('delete', 'App\\Models\User')
                                        <form action="{{ route('users.destroy', $user->id) }}" method="post" onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
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
                            <td colspan="8" class="adm-empty">لا يوجد مستخدمون</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($users->hasPages())
            <div class="adm-pager">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</x-front-layout>
