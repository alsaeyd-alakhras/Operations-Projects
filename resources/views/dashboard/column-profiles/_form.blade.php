@push('styles')
    <link rel="stylesheet" href="{{ asset('css/records.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-pages.css') }}">
    <script>
        try { document.documentElement.classList.toggle('rec-dark', localStorage.getItem('mode') === 'dark'); } catch (e) {}
    </script>
@endpush
@php
    $selected = old('columns', $profile->exists || $errors->any() ? ($profile->columns ?? []) : []);
    $restricted = old('restricted', $profile->restricted);
@endphp
<div class="rec-form rec-form--page">
    <div class="rec-form__body">
        <section class="rec-section">
            <h6 class="rec-section__title">بيانات الملف</h6>
            <div class="row">
                <div class="form-group col-md-4">
                    <x-form.input label="اسم الملف" :value="$profile->name" name="name" placeholder="مالية" required autofocus/>
                </div>
            </div>
        </section>
        <section class="rec-section">
            <h6 class="rec-section__title">الأعمدة</h6>
            <p class="text-muted small mb-2">اسم المريض يظهر دائمًا. حدّد الأعمدة التي يشملها الملف.</p>
            @error('columns')<div class="text-danger small mb-2">{{ $message }}</div>@enderror
            <div class="rec-perms">
                @foreach (\App\Models\ColumnProfile::GROUPS as $groupName => $groupColumns)
                    <div class="rec-perm-group">
                        <h6 class="rec-perm-group__title">{{ $groupName }}</h6>
                        @foreach ($groupColumns as $column)
                            <label class="rec-perm" for="col-{{ $column }}">
                                <input type="checkbox" name="columns[]" id="col-{{ $column }}" value="{{ $column }}" @checked(in_array($column, (array) $selected, true))>
                                {{ \App\Models\ColumnProfile::COLUMNS[$column] }}
                            </label>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
        <section class="rec-section">
            <h6 class="rec-section__title">التقييد</h6>
            <input type="hidden" name="restricted" value="0">
            <label class="rec-check" for="restricted">
                <input type="checkbox" name="restricted" id="restricted" value="1" @checked($restricted)>
                مقيّد من السيرفر
            </label>
            <p class="text-muted small mt-2 mb-0">
                غير مقيّد: الأعمدة المحددة هي العرض الافتراضي فقط، ويستطيع المستخدم إظهار أي عمود آخر من قائمة «الأعمدة».<br>
                مقيّد: لا تُرسل الأعمدة غير المحددة من السيرفر أبدًا، ولا تظهر في الجدول أو التصفية أو التصدير أو الطباعة (مناسب لحسابات الفروع). المدير العام لا يتأثر بالتقييد.
            </p>
        </section>
    </div>
    <div class="rec-form__foot">
        <a href="{{ route('column-profiles.index') }}" class="rec-btn"><i class="fe fe-x"></i> إلغاء</a>
        <button type="submit" class="rec-btn rec-btn--primary">
            <i class="fe fe-check"></i> {{ $btn_label ?? ($profile->exists ? 'حفظ التعديلات' : 'أضف') }}
        </button>
    </div>
</div>
