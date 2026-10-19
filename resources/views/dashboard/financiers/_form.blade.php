@push('styles')
    <link rel="stylesheet" href="{{ asset('css/records.css') }}">
    <link rel="stylesheet" href="{{ asset('css/admin-pages.css') }}">
    <script>
        try { document.documentElement.classList.toggle('rec-dark', localStorage.getItem('mode') === 'dark'); } catch (e) {}
    </script>
@endpush
<div class="rec-form rec-form--page">
    <div class="rec-form__body">
        <section class="rec-section">
            <h6 class="rec-section__title">بيانات الممول</h6>
            <div class="row">
                <div class="form-group col-md-4">
                    <x-form.input label="الاسم" :value="$financier->name" name="name" placeholder="اسم الممول" required autofocus />
                </div>
                <div class="form-group col-md-4">
                    <x-form.input type="number" list="financier_numbers_list" :value="$financier->financier_number" name="financier_number" label="رقم الممول" placeholder="" required />
                    <datalist id="financier_numbers_list">
                        @foreach ($financier_numbers as $financier_number)
                            <option value="{{$financier_number}}">
                        @endforeach
                    </datalist>
                </div>
                <div class="form-group col-md-4">
                    <x-form.input :value="$financier->stage" name="stage" label="المرحلة" placeholder="" required />
                </div>
                <div class="form-group col-md-4">
                    <x-form.input :value="$financier->maneger_name" name="maneger_name" label="المدير" placeholder="" />
                </div>
                <div class="form-group col-md-4">
                    <x-form.input type="number" step="0.01" min="0" :value="$financier->amount_ils" name="amount_ils" label="المبلغ ش" placeholder="" required />
                </div>
                <div class="form-group col-md-4">
                    <x-form.input type="number" min="0" :value="$financier->number_cases" name="number_cases" label="عدد الحالات" placeholder="" required />
                </div>
            </div>
        </section>
        <section class="rec-section">
            <h6 class="rec-section__title">حالة المشروع</h6>
            <div class="rec-check-grid">
                <label class="rec-check" for="completion_project">
                    <input type="checkbox" id="completion_project" name="completion_project" @checked($financier->completion_project == 1)> إنتهاء المشروع
                </label>
                <label class="rec-check" for="push_project">
                    <input type="checkbox" id="push_project" name="push_project" @checked($financier->push_project == 1)> دفع المشروع
                </label>
                <label class="rec-check" for="project_distribution">
                    <input type="checkbox" id="project_distribution" name="project_distribution" @checked($financier->project_distribution == 1)> توزيع المشروع
                </label>
                <label class="rec-check" for="project_archive">
                    <input type="checkbox" id="project_archive" name="project_archive" @checked($financier->project_archive == 1)> أرشفة المشروع
                </label>
            </div>
        </section>
    </div>
    <div class="rec-form__foot">
        <a href="{{ route('financiers.index') }}" class="rec-btn"><i class="fe fe-x"></i> إلغاء</a>
        <button type="submit" class="rec-btn rec-btn--primary">
            <i class="fe fe-check"></i> {{ $btn_label ?? ($financier->exists ? 'حفظ التعديلات' : 'أضف') }}
        </button>
    </div>
</div>
