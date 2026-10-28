<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    <div class="adm-page">
        <div class="adm-head">
            <h2 class="adm-title page-title">إضافة ملف أعمدة جديد</h2>
        </div>
        <form action="{{route('column-profiles.store')}}" method="post">
            @csrf
            @include("dashboard.column-profiles._form")
        </form>
    </div>
</x-front-layout>
