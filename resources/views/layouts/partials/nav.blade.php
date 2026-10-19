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
</style>
<nav class="navbar navbar-expand-lg navbar-light bg-dark flex-row border-bottom shadow p-0">
    <div class="container-fluid">
        <a class="navbar-brand mx-lg-1 mr-0 d-flex align-items-center p-0" href="{{route('records.index')}}}">
            <img src="{{asset('img/logo.png')}}" class="navbar-brand-img" alt="..." style="width: 8%">
            <h1 class="h3 ml-2 mb-0 text-white">مستشفى يافا الطبي</h1>
        </a>
        <button class="navbar-toggler mt-2 mr-auto toggle-sidebar text-muted">
            <i class="fe fe-menu navbar-toggler-icon"></i>
        </button>
        <div class="navbar-slide bg-dark ml-lg-4" id="navbarSupportedContent">
            <a href="#" class="btn toggle-sidebar d-lg-none text-muted ml-2 mt-3" data-toggle="toggle">
                <i class="fe fe-x"><span class="sr-only"></span></i>
            </a>
            <ul class="navbar-nav mr-auto">
                @can('view','App\\Models\Record')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-start" href="{{route('records.index')}}">
                        <i class="fe fe-layers fe-16"></i>
                        <span class="ml-lg-2">السجلات</span>
                        {{-- <span class="badge badge-pill badge-info">{{ App\Models\Record::count() }}</span> --}}
                    </a>
                </li>
                @endcan
                @can('archive','App\\Models\Record')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-start" href="{{route('records.archived')}}">
                        <i class="fe fe-archive fe-16"></i>
                        <span class="ml-lg-2">الأرشيف</span>
                        {{-- <span class="badge badge-pill badge-info">{{ App\Models\Record::count() }}</span> --}}
                    </a>
                </li>
                @endcan
                @can('view','App\\Models\Financier')
                <li class="nav-item">
                    <a class="nav-link  d-flex align-items-start" href="{{route('financiers.index')}}">
                        <i class="fe fe-dollar-sign fe-16"></i>
                        <span class="ml-lg-2">الممولين</span>
                    </a>
                </li>
                @endcan
                @can('view','App\\Models\User')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-start" href="{{route('users.index')}}">
                        <i class="fe fe-user fe-16"></i>
                        <span class="ml-lg-2">المستخدمين</span>
                    </a>
                </li>
                @endcan
                @can('view','App\\Models\Branch')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-start" href="{{route('branches.index')}}">
                        <i class="fe fe-map-pin fe-16"></i>
                        <span class="ml-lg-2">الفروع</span>
                    </a>
                </li>
                @endcan
                @can('view','App\\Models\Logs')
                <li class="nav-item">
                    <a class="nav-link d-flex align-items-start" href="{{route('logs.index')}}">
                        <i class="fe fe-clipboard fe-16"></i>
                        <span class="ml-lg-2">الأحداث</span>
                    </a>
                </li>
                @endcan
            </ul>
        </div>
        <ul class="navbar-nav d-flex flex-row">
            {{ $extra_nav ?? '' }}
            <li class="nav-item">
                <li class="nav-item">
                    <a class="nav-link text-muted my-2" href="#" id="modeSwitcher" data-mode="light">
                        <i class="fe fe-sun fe-16"></i>
                    </a>
                </li>
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
