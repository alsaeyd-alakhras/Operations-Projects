<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    <div class="adm-page">
        <div class="adm-head">
            <h2 class="adm-title page-title">إضافة فرع جديد</h2>
        </div>
        <form action="{{route('branches.store')}}" method="post">
            @csrf
            @include("dashboard.branches._form")
        </form>
    </div>
</x-front-layout>
