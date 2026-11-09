<style>
    .navbar-light .navbar-nav .nav-item > .nav-link{
        color: #fff !important;
        transition: all 0.3s ease-in-out;
    }
    .navbar-light .navbar-nav .nav-item > .nav-link:hover {
        color: #fff !important;
        background-color: #303030 !important;
    }
    .dropdown-menu .nav-link{
        color: #000 !important;
    }
    .dropdown-menu .nav-link:hover{
        color: #fff !important;
        background-color: #303030 !important;
    }
    .navbar-light .navbar-nav .nav-item > .nav-link.active { background-color: #303030 !important; }
    .rec-menu .dropdown-item.active { background: var(--rec-brand-tint); color: var(--rec-brand-strong); }
    .nav-brand-title { font-size: 1.5rem; font-weight: 600; white-space: nowrap; }
    @media (max-width: 1399.98px) { .nav-brand-title { font-size: 1.2rem; } }
    @media (min-width: 992px) and (max-width: 1199.98px) { .nav-brand-title { display: none; } }
</style>
<nav class="navbar navbar-expand-lg navbar-light bg-dark flex-row border-bottom shadow p-0">
    <div class="container-fluid">
        <a class="navbar-brand mx-lg-1 mr-0 d-flex align-items-center p-0" href="{{ route('dashboard') }}">
            <img src="{{asset('img/logo-wh.png')}}" class="navbar-brand-img" alt="شعار" style="height: 44px; width: auto; max-width: none;">
            <h1 class="nav-brand-title ml-2 mb-0 text-white">القطاع الصحي - متابعة الحالات</h1>
        </a>
        <button class="navbar-toggler mt-2 mr-auto toggle-sidebar text-muted">
            <i class="fe fe-menu navbar-toggler-icon"></i>
        </button>
        <div class="navbar-slide bg-dark ml-lg-4" id="navbarSupportedContent">
            <a href="#" class="btn toggle-sidebar d-lg-none text-muted ml-2 mt-3" data-toggle="toggle">
                <i class="fe fe-x"><span class="sr-only"></span></i>
            </a>
            <ul class="navbar-nav mr-auto">
                @php
                    $navUser = auth()->user();
                    $kwArchived = request()->routeIs('kuwaiti-cases.index') && request()->boolean('archived');
                    $navGroups = [
                        ['label' => 'يافا', 'icon' => 'fe-layers', 'items' => array_values(array_filter([
                            $navUser->can('view', \App\Models\Record::class) ? ['label' => 'السجلات', 'icon' => 'fe-layers', 'url' => route('records.index'), 'active' => request()->routeIs('records.index')] : null,
                            $navUser->can('archive', \App\Models\Record::class) ? ['label' => 'الأرشيف', 'icon' => 'fe-archive', 'url' => route('records.archived'), 'active' => request()->routeIs('records.archived')] : null,
                            $navUser->can('view', \App\Models\Financier::class) ? ['label' => 'الممولين', 'icon' => 'fe-dollar-sign', 'url' => route('financiers.index'), 'active' => request()->routeIs('financiers.*')] : null,
                        ]))],
                        ['label' => 'الكويتي', 'icon' => 'fe-activity', 'items' => array_values(array_filter([
                            $navUser->can('view', \App\Models\KuwaitiCase::class) ? ['label' => 'حالات الكويتي', 'icon' => 'fe-activity', 'url' => route('kuwaiti-cases.index'), 'active' => request()->routeIs('kuwaiti-cases.*') && ! $kwArchived] : null,
                            $navUser->can('archive', \App\Models\KuwaitiCase::class) ? ['label' => 'أرشيف الكويتي', 'icon' => 'fe-archive', 'url' => route('kuwaiti-cases.index', ['archived' => 1]), 'active' => $kwArchived] : null,
                        ]))],
                    ];
                @endphp
                @foreach ($navGroups as $gi => $group)
                    @continue(count($group['items']) === 0)
                    @if (count($group['items']) === 1)
                        @php($item = $group['items'][0])
                        <li class="nav-item">
                            <a class="nav-link d-flex align-items-start {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}">
                                <i class="fe {{ $item['icon'] }} fe-16"></i>
                                <span class="ml-lg-2">{{ $item['label'] }}</span>
                            </a>
                        </li>
                    @else
                        <li class="nav-item dropdown">
                            <a class="nav-link d-flex align-items-start dropdown-toggle {{ collect($group['items'])->contains('active', true) ? 'active' : '' }}" href="#" id="navGroup{{ $gi }}" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fe {{ $group['icon'] }} fe-16"></i>
                                <span class="ml-lg-2">{{ $group['label'] }}</span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-right rec-menu" aria-labelledby="navGroup{{ $gi }}">
                                @foreach ($group['items'] as $item)
                                    <a class="dropdown-item {{ $item['active'] ? 'active' : '' }}" href="{{ $item['url'] }}"><i class="fe {{ $item['icon'] }} fe-16"></i> {{ $item['label'] }}</a>
                                @endforeach
                            </div>
                        </li>
                    @endif
                @endforeach
                @if (auth()->user()->can('view','App\\Models\User') || auth()->user()->can('view','App\\Models\ColumnProfile') || auth()->user()->can('view','App\\Models\Logs') || auth()->user()->can('view','App\\Models\Branch'))
                <li class="nav-item dropdown">
                    <a class="nav-link d-flex align-items-start dropdown-toggle" href="#" id="settingsMenu" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        <i class="fe fe-settings fe-16"></i>
                        <span class="ml-lg-2">الإعدادات</span>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right rec-menu" aria-labelledby="settingsMenu">
                        @can('view','App\\Models\User')
                        <a class="dropdown-item" href="{{route('users.index')}}"><i class="fe fe-user fe-16"></i> المستخدمين</a>
                        @endcan
                        @can('view','App\\Models\ColumnProfile')
                        <a class="dropdown-item" href="{{route('column-profiles.index')}}"><i class="fe fe-columns fe-16"></i> الأدوار</a>
                        @endcan
                        @can('view','App\\Models\Branch')
                        <a class="dropdown-item" href="{{route('branches.index')}}"><i class="fe fe-map-pin fe-16"></i> الفروع</a>
                        @endcan
                        @can('view','App\\Models\Logs')
                        <a class="dropdown-item" href="{{route('logs.index')}}"><i class="fe fe-clipboard fe-16"></i> الأحداث</a>
                        @endcan
                    </div>
                </li>
                @endif
            </ul>
        </div>
        <ul class="navbar-nav d-flex flex-row">
            {{ $extra_nav ?? '' }}
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle text-muted p-0" href="#" id="navbarDropdownMenuLink"
                    role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    <span class="avatar avatar-sm mt-2">
                        <img src="{{asset('img/user.jpg')}}" alt="..." class="avatar-img rounded-circle">
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="dropdown-item">تسجيل الخروج</button>
                    </form>
                </div>
            </li>
        </ul>
    </div>
</nav>
