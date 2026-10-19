<div class="rec-form__head">
    <h5 class="rec-form__title" id="recordFormTitle">السجل</h5>
    <button type="button" class="rec-form__x" data-dismiss="modal" aria-label="إغلاق"><i class="fe fe-x"></i></button>
</div>
<div class="rec-form__body">
    <div class="alert alert-danger" role="alert" id="alerts" style="display: none;"></div>

    <section class="rec-section">
        <h6 class="rec-section__title">بيانات المريض</h6>
        <div class="row">
            <div class="form-group col-md-6">
                <x-form.input name="name" label="اسم المريض" required list="names_list" :disabled="auth()->user()->isBranchUser()" />
                <datalist id="names_list">
                    @foreach ($names as $name)
                        <option value="{{$name}}">
                    @endforeach
                </datalist>
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="patient_ID" maxlength="9" label="رقم الهوية" required :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input type="number" step="0.1" name="age" min="0" label="عمر المريض" required :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="phone_number1" maxlength="10" label="جوال 1" required :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="phone_number2" maxlength="10" label="جوال 2"  :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-6">
                <x-form.input name="address" label="العنوان" :disabled="auth()->user()->isBranchUser()" />
            </div>
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">الحالة الاجتماعية</h6>
        <div class="row">
            <div class="form-group col-md-6">
                <label for="displacement_status">حالة النزوح والسكن وتضرر البيت</label>
                <select name="displacement_status" id="displacement_status" class="form-control form-control-alternative" {{ auth()->user()->isBranchUser() ? 'disabled' : '' }}>
                    <option value=""></option>
                    @foreach (\App\Models\Record::DISPLACEMENT_STATUSES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="income_status">مصادر الدخل والوضع المالي</label>
                <select name="income_status" id="income_status" class="form-control form-control-alternative" {{ auth()->user()->isBranchUser() ? 'disabled' : '' }}>
                    <option value=""></option>
                    @foreach (\App\Models\Record::INCOME_STATUSES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="family_burden">حجم الأسرة والحالات الخاصة</label>
                <select name="family_burden" id="family_burden" class="form-control form-control-alternative" {{ auth()->user()->isBranchUser() ? 'disabled' : '' }}>
                    <option value=""></option>
                    @foreach (\App\Models\Record::FAMILY_BURDENS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="distributed">تم التوزيع</label>
                <select name="distributed" id="distributed" class="form-control form-control-alternative" {{ auth()->user()->isBranchUser() ? 'disabled' : '' }}>
                    <option value=""></option>
                    <option value="1">نعم</option>
                    <option value="0">لا</option>
                </select>
            </div>
            @if (!auth()->user()->isBranchUser())
            <div class="form-group col-md-3">
                <label for="branch_id">الفرع</label>
                <select name="branch_id" id="branch_id" class="form-control form-control-alternative">
                    <option value="">بدون فرع</option>
                    @foreach (\App\Models\Branch::orderBy('name')->get() as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="form-group col-md-3">
                <label for="eligible">يستحق</label>
                <select name="eligible" id="eligible" class="form-control form-control-alternative" {{ auth()->user()->isBranchUser() ? '' : 'disabled' }}>
                    <option value="">لم يُحدد</option>
                    <option value="1">نعم</option>
                    <option value="0">لا</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="branch_notes">ملاحظات الفرع</label>
                <textarea name="branch_notes" id="branch_notes" rows="2" class="form-control form-control-alternative" {{ auth()->user()->isBranchUser() ? '' : 'disabled' }}></textarea>
            </div>
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">العملية</h6>
        <div class="row">
            <div class="form-group col-md-3">
                @can('date','App\\Models\Record')
                    <x-form.input type="date" name="date" label="التاريخ"  required :disabled="auth()->user()->isBranchUser()" />
                @else
                    <x-form.input type="date" name="date" label="التاريخ" readonly :disabled="auth()->user()->isBranchUser()" />
                @endcan
            </div>
            <div class="form-group col-md-3">
                <x-form.input type="number" name="financier_number" list="financier_numbers_list" label="رقم الممول" :disabled="auth()->user()->isBranchUser()" />
                <datalist id="financier_numbers_list">
                    @foreach ($financiers as $financier)
                        <option value="{{$financier}}">
                    @endforeach
                </datalist>
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="operation" list="operations_list" label="العملية" required :disabled="auth()->user()->isBranchUser()" />
                <datalist id="operations_list">
                    @foreach ($operations as $operation)
                        <option value="{{$operation}}">
                    @endforeach
                </datalist>
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="doctor" list="doctors_list" label="اسم الطبيب" required :disabled="auth()->user()->isBranchUser()" />
                <datalist id="doctors_list">
                    @foreach ($doctors as $doctor)
                        <option value="{{$doctor}}">
                    @endforeach
                </datalist>
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="anesthesia" list="anesthesias_list" label="طبيب التخدير" :disabled="auth()->user()->isBranchUser()" />
                <datalist id="anesthesias_list">
                    @foreach ($anesthesias as $anesthesia)
                        <option value="{{$anesthesia}}">
                    @endforeach
                </datalist>
            </div>
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">المالية</h6>
        <div class="row">
            <div class="form-group col-md-3">
                <x-form.input type="number" min="0" name="amount" label="م مطلوب"  required :disabled="auth()->user()->isBranchUser()" />
            </div>
            @can('financial','App\\Models\Record')
            <div class="form-group col-md-3">
                <x-form.input type="number" min="0" name="doctor_share" label="حصة الطبيب" :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input type="number" min="0" name="anesthesiologists_share" label="حصة طبيب التخدير" value="0" :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input type="number" min="0" name="bed" label="المبيت" value="0" :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input type="number" min="0" name="private" label="خاص" :disabled="auth()->user()->isBranchUser()" />
            </div>
            @endcan
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">ملاحظات وحالة</h6>
        <div class="row">
            <div class="form-group col-md-6">
                <x-form.input type="text" name="notes" label="ملاحظات" :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-6">
                <x-form.input type="text" name="notes_2" label="ملاحظات ثانوية" :disabled="auth()->user()->isBranchUser()" />
            </div>
            <div class="form-group col-md-3">
                <label class="rec-check" for="done"><input type="checkbox" name="done" id="done" {{ auth()->user()->isBranchUser() ? 'disabled' : '' }}> تمت العملية</label>
            </div>
            <div class="form-group col-md-3 editForm">
                <x-form.input name="user_name" label="اسم المستخدم"  disabled />
            </div>
        </div>
    </section>
</div>

<div class="rec-form__foot" id="btns_form">
    @can('import','App\\Models\Record')
    @if (!auth()->user()->isBranchUser())
    <button type="button" class="rec-btn" id="import_excel_btn"><i class="fe fe-upload"></i> رفع ملف اكسيل</button>
    @endif
    @endcan
    <button type="button" class="rec-btn" data-dismiss="modal"><i class="fe fe-x"></i> إلغاء</button>
    <button type="button" id="update" class="btn rec-btn rec-btn--primary"><i class="fe fe-check"></i> حفظ التعديلات</button>
</div>
