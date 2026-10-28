<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    <div class="adm-page">
        <div class="adm-head">
            <h2 class="adm-title page-title">تعديل ملف الأعمدة : {{$profile->name}}</h2>
        </div>
        <form action="{{route('column-profiles.update',$profile->id)}}" method="post">
            @csrf
            @method('put')
            @include("dashboard.column-profiles._form")
        </form>
    </div>
</x-front-layout>
