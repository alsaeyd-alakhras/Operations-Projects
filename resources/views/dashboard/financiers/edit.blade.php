<x-front-layout classC="shadow p-3 mb-5 bg-white rounded ">
    <div class="adm-page">
        <div class="adm-head">
            <h2 class="adm-title page-title">تعديل بيانات ممول  : {{$financier->name}}</h2>
        </div>
        <form action="{{route('financiers.update',$financier->id)}}" method="post">
            @csrf
            @method('put')
            @include("dashboard.financiers._form")
        </form>
    </div>
</x-front-layout>
