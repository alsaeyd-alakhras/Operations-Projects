@php
    use App\Models\KuwaitiCase;
    $isBranch = auth()->user()->isBranchUser();
    $lock = $isBranch ? 'disabled' : '';
    $groups = [
        ['field' => 'exam_need', 'title' => 'تفاصيل الفحص التشخيصي ومدى الحاجة إليه', 'weight' => 30, 'options' => KuwaitiCase::EXAM_NEEDS],
        ['field' => 'housing_status', 'title' => 'حالة النزوح والسكن وتضرر المسكن', 'weight' => 30, 'options' => KuwaitiCase::HOUSING_STATUSES],
        ['field' => 'income_status', 'title' => 'مصادر الدخل والوضع المالي', 'weight' => 30, 'options' => KuwaitiCase::INCOME_STATUSES],
        ['field' => 'family_burden', 'title' => 'حجم الأسرة والحالات الاجتماعية الخاصة', 'weight' => 10, 'options' => KuwaitiCase::FAMILY_BURDENS],
    ];
@endphp
<div class="rec-form__head">
    <h5 class="rec-form__title" id="recordFormTitle">حالة</h5>
    <button type="button" class="rec-form__x" data-dismiss="modal" aria-label="إغلاق"><i class="fe fe-x"></i></button>
</div>
<div class="rec-form__body">
    <div class="alert alert-danger" role="alert" id="alerts" style="display: none;"></div>

    <section class="rec-section">
        <h6 class="rec-section__title">بيانات المريض</h6>
        <div class="row">
            <div class="form-group col-md-6">
                <x-form.input name="name" label="اسم المريض رباعي" required :disabled="$isBranch" />
            </div>
            <div class="form-group col-md-3">
                <label for="gender">الجنس</label>
                <select name="gender" id="gender" class="form-control form-control-alternative" required {{ $lock }}>
                    <option value=""></option>
                    @foreach (KuwaitiCase::GENDERS as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="patient_ID" maxlength="9" label="رقم الهوية" required :disabled="$isBranch" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input type="date" name="birth_date" label="تاريخ الميلاد" :disabled="$isBranch" />
            </div>
            <div class="form-group col-md-3">
                <label for="marital_status">الحالة الاجتماعية</label>
                <select name="marital_status" id="marital_status" class="form-control form-control-alternative" {{ $lock }}>
                    <option value=""></option>
                    @foreach (KuwaitiCase::MARITAL_STATUSES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-3">
                <x-form.input name="phone_number" maxlength="10" label="رقم للتواصل" :disabled="$isBranch" />
            </div>
            <div class="form-group col-md-3">
                <x-form.input type="date" name="date" label="التاريخ" required :disabled="$isBranch" />
            </div>
            <div class="form-group col-md-6">
                <x-form.input name="address" label="العنوان الحالي بالضبط ( المدينة - المنطقة - أقرب معلم )" :disabled="$isBranch" />
            </div>
            <div class="form-group col-md-6">
                <x-form.input name="displacement_address" label="عنوان السكن قبل النزوح" :disabled="$isBranch" />
            </div>
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">الفحص المطلوب</h6>
        <div class="row">
            <div class="form-group col-md-8">
                <x-form.input name="exam" label="الفحص المطلوب" :disabled="$isBranch" />
            </div>
            <div class="form-group col-md-4">
                <x-form.input type="number" min="0" step="0.01" name="exam_cost" label="تكلفة الفحص" :disabled="$isBranch" />
            </div>
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">تقييم الحالة</h6>
        <div class="kw-eval">
            @foreach ($groups as $group)
                <div class="kw-group" data-kw-group="{{ $group['field'] }}">
                    <h6 class="kw-group__title"><span>{{ $group['title'] }}</span><small>(الوزن: {{ $group['weight'] }} نقطة)</small></h6>
                    <div class="kw-cards">
                        @foreach ($group['options'] as $key => $option)
                            <label class="kw-card">
                                <input type="radio" name="{{ $group['field'] }}" id="{{ $group['field'] }}_{{ $key }}" value="{{ $key }}" data-points="{{ $option['points'] }}" {{ $lock }}>
                                <span class="kw-card__body"><span>{{ $option['label'] }}</span><span class="kw-card__pts">{{ $option['points'] }}</span></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endforeach
            <div class="kw-total" aria-live="polite">
                <span class="kw-total__label">إجمالي النقاط</span>
                <span><span class="kw-total__value" id="kw_total_score">0</span> <span class="kw-total__max">/ 100</span></span>
            </div>
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">وصف الحالة</h6>
        <div class="row">
            <div class="form-group col-md-12">
                <label for="case_description">وصف الحالة (الوضع الإنساني والمعيشي للأسرة، وعدد أفرادها، ومصدر الدخل، وأبرز الصعوبات، والاحتياجات الأساسية)</label>
                <textarea name="case_description" id="case_description" rows="3" class="form-control form-control-alternative" {{ $lock }}></textarea>
            </div>
            <div class="form-group col-md-4">
                <x-form.input name="data_entry" label="مدخل البيانات" :disabled="$isBranch" />
            </div>
            @if (!$isBranch)
            <div class="form-group col-md-4">
                <label for="branch_id">الفرع</label>
                <select name="branch_id" id="branch_id" class="form-control form-control-alternative">
                    <option value="">بدون فرع</option>
                    @foreach (\App\Models\Branch::orderBy('name')->get() as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
        </div>
    </section>

    <section class="rec-section">
        <h6 class="rec-section__title">رأي الفرع</h6>
        <div class="row">
            <div class="form-group col-md-4">
                <label for="eligible">رأي مدير المكتب</label>
                <select name="eligible" id="eligible" class="form-control form-control-alternative" {{ $isBranch ? '' : 'disabled' }}>
                    <option value="">غير محدد</option>
                    <option value="1">يستحق</option>
                    <option value="0">لا يستحق</option>
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="need_percentage">نسبة الاحتياج</label>
                <select name="need_percentage" id="need_percentage" class="form-control form-control-alternative" {{ $isBranch ? '' : 'disabled' }}>
                    <option value=""></option>
                    @foreach (KuwaitiCase::NEED_PERCENTAGES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-12">
                <label for="branch_notes">ملاحظات الفرع</label>
                <textarea name="branch_notes" id="branch_notes" rows="2" class="form-control form-control-alternative" {{ $isBranch ? '' : 'disabled' }}></textarea>
            </div>
        </div>
    </section>

    @can('done', 'App\\Models\\KuwaitiCase')
    <section class="rec-section">
        <h6 class="rec-section__title">حالة الفحص</h6>
        <div class="row">
            <div class="form-group col-md-3">
                <label class="rec-check" for="done"><input type="checkbox" name="done" id="done"> تمت</label>
            </div>
        </div>
    </section>
    @endcan

    @can('management', 'App\\Models\\KuwaitiCase')
    <section class="rec-section">
        <h6 class="rec-section__title">موافقة الإدارة</h6>
        <div class="row">
            <div class="form-group col-md-3">
                <label for="management_status">حالة موافقة الإدارة</label>
                <select name="management_status" id="management_status" class="form-control form-control-alternative">
                    <option value="">لم يحدد</option>
                    @foreach (\App\Models\Record::MANAGEMENT_STATUSES as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-9">
                <label for="management_notes">ملاحظات الإدارة</label>
                <textarea name="management_notes" id="management_notes" rows="2" class="form-control form-control-alternative"></textarea>
            </div>
        </div>
    </section>
    @endcan
</div>

<div class="rec-form__foot" id="btns_form">
    @can('import', 'App\\Models\\KuwaitiCase')
    @if (!$isBranch)
    <button type="button" class="rec-btn" id="import_excel_btn"><i class="fe fe-upload"></i> رفع ملف اكسيل</button>
    @endif
    @endcan
    <button type="button" class="rec-btn" data-dismiss="modal"><i class="fe fe-x"></i> إلغاء</button>
    <button type="button" id="update" class="btn rec-btn rec-btn--primary"><i class="fe fe-check"></i> حفظ التعديلات</button>
</div>
