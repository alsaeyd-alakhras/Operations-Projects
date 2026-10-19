<aside class="sidebar-left border-right bg-white shadow" id="leftSidebar" data-simplebar>
    <a href="#" class="btn collapseSidebar toggle-btn d-lg-none text-muted ml-2 mt-3" data-toggle="toggle">
        <i class="fe fe-x"><span class="sr-only"></span></i>
    </a>
    <nav class="vertnav navbar navbar-light">
        <!-- nav bar -->
        <div class="row justify-content-center" style="text-align:center;">
            <div class="w-100 mb-4 d-flex">
                <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="./index.html">
                    <img src="{{asset('img/logo.png')}}" class="navbar-brand-img mx-auto" alt="..." style="width: 75%">
                </a>
            </div>
            <h2>{{Config::get('app.name')}}</h2>
        </div>
        <p class="text-muted nav-heading mt-4 mb-1">
            <span>العام</span>
        </p>
        <ul class="navbar-nav flex-fill w-100 mb-2">
            @can('view','App\\Models\Record')
            <li class="nav-item w-100">
                <a class="nav-link" href="{{route('records.index')}}">
                    <i class="fe fe-layers fe-16"></i>
                    <span class="ml-3 item-text">السجلات</span>
                </a>
            </li>
            @endcan
            @can('view','App\\Models\Financier')
            <li class="nav-item w-100">
                <a class="nav-link" href="{{route('financiers.index')}}">
                    <i class="fe fe-dollar-sign fe-16"></i>
                    <span class="ml-3 item-text">الممولين</span>
                </a>
            </li>
            @endcan
        </ul>
        <p class="text-muted nav-heading mt-4 mb-1">
            <span>الإعدادات</span>
        </p>
        <ul class="navbar-nav flex-fill w-100 mb-2">
            @can('view','App\\Models\User')
            <li class="nav-item w-100">
                <a class="nav-link" href="{{route('users.index')}}">
                    <i class="fe fe-user fe-16"></i>
                    <span class="ml-3 item-text">المستخدمين</span>
                </a>
            </li>
            @endcan
            @can('view','App\\Models\Branch')
            <li class="nav-item w-100">
                <a class="nav-link" href="{{route('branches.index')}}">
                    <i class="fe fe-map-pin fe-16"></i>
                    <span class="ml-3 item-text">الفروع</span>
                </a>
            </li>
            @endcan
            @can('view','App\\Models\Logs')
            <li class="nav-item w-100">
                <a class="nav-link" href="{{route('logs.index')}}">
                    <i class="fe fe-user fe-16"></i>
                    <span class="ml-3 item-text">الأحداث</span>
                </a>
            </li>
            @endcan
        </ul>
        <div class="btn-box w-100 mt-3 mb-1">
            <p class="text-muted font-weight-bold h6">© تم الإنشاء بواسطة <a href="https://saeyd-jamal.github.io/My_Portfolio/" target="_blank">م.السيد الأخرس</a></p>
        </div>
    </nav>
</aside>
