<div class="alert alert-danger" role="alert"  id="alerts" style="display: none;">

</div>
<div class="row">
    <style>
        .form-group label:first-of-type{
            font-size: 20px;
            color: #000;
        }
        .form-group label:nth-of-type(2){
            color: red;
        }
    </style>
    <div class="form-group col-md-12">
        <x-form.input name="name" label="اسم المريض" required list="names_list" />
        <datalist id="names_list">
            @foreach ($names as $name)
                <option value="{{$name}}">
            @endforeach
        </datalist>
    </div>
    <div class="form-group col-md-3">
        @can('date','App\\Models\Record')
            <x-form.input type="date" name="date" label="التاريخ"  required />
        @else
            <x-form.input type="date" name="date" label="التاريخ" readonly />
        @endcan
    </div>
    <div class="form-group col-md-3">
        <x-form.input name="patient_ID" maxlength="9" label="رقم الهوية" required />
    </div>
    <div class="form-group col-md-3">
        <x-form.input type="number" step="0.1" name="age" min="0" label="عمر المريض" required />
    </div>
    <div class="form-group col-md-3">
        <x-form.input name="address" label="العنوان" />
    </div>
    <div class="form-group col-md-3">
        <x-form.input name="phone_number1" maxlength="10" label="جوال 1" required />
    </div>
    <div class="form-group col-md-3">
        <x-form.input name="phone_number2" maxlength="10" label="جوال 2"  />
    </div>
    <div class="form-group col-md-3">
        <x-form.input type="number" name="financier_number" list="financier_numbers_list" label="رقم الممول" />
        <datalist id="financier_numbers_list">
            @foreach ($financiers as $financier)
                <option value="{{$financier}}">
            @endforeach
        </datalist>
    </div>
    <div class="form-group col-md-4">
        <x-form.input name="operation" list="operations_list" label="العملية" required />
        <datalist id="operations_list">
            @foreach ($operations as $operation)
                <option value="{{$operation}}">
            @endforeach
        </datalist>
    </div>
    <div class="form-group col-md-4">
        <x-form.input name="doctor" list="doctors_list" label="اسم الطبيب" required />
        <datalist id="doctors_list">
            @foreach ($doctors as $doctor)
                <option value="{{$doctor}}">
            @endforeach
        </datalist>
    </div>
    <div class="form-group col-md-4">
        <x-form.input name="anesthesia" list="anesthesias_list" label="طبيب التخدير" />
        <datalist id="anesthesias_list">
            @foreach ($anesthesias as $anesthesia)
                <option value="{{$anesthesia}}">
            @endforeach
        </datalist>
    </div>
</div>
<hr>
<div class="row">
    <div class="form-group col-md-3">
        <x-form.input type="number" min="0" name="amount" label="م مطلوب"  required />
    </div>
    @can('financial','App\\Models\Record')
    <div class="form-group col-md-3">
        <x-form.input type="number" min="0" name="doctor_share" label="حصة الطبيب" />
    </div>
    <div class="form-group col-md-3">
        <x-form.input type="number" min="0" name="anesthesiologists_share" label="حصة طبيب التخدير" value="0" />
    </div>
    <div class="form-group col-md-3">
        <x-form.input type="number" min="0" name="bed" label="المبيت" value="0" />
    </div>
    <div class="form-group col-md-3">
        <x-form.input type="number" min="0" name="private" label="خاص" />
    </div>
    @endcan
</div>
<hr>
<div class="row">
    <div class="form-group col-md-6">
        <x-form.input type="text" name="notes" label="ملاحظات" />
    </div>
    <div class="form-group col-md-6">
        <x-form.input type="text" name="notes_2" label="ملاحظات ثانوية" />
    </div>
    <div class="form-group col-md-3">
        <input type="checkbox" name="done" id="done" >
        <label for="done">تمت العملية</label>
    </div>
    <div class="form-group col-md-3 editForm">
        <x-form.input name="user_name" label="اسم المستخدم"  disabled />
    </div>
</div>
<hr>
<div class="row d-flex justify-content-end" id="btns_form">
    @can('import','App\\Models\Record')
    <button type="button" class="btn mb-2 btn-secondary mx-2" id="import_excel_btn">
        <i class="fe fe-upload"></i>
        رفع ملف اكسيل
    </button>
    @endcan
    <button aria-label="" type="button" class="btn btn-danger px-2" data-dismiss="modal" aria-hidden="true">
        <span aria-hidden="true">×</span>
        إغلاق
    </button>
    <button type="button" id="update" class="btn btn-primary mx-2">
        <i class="fe fe-edit"></i>
        تعديل
    </button>
</div>
