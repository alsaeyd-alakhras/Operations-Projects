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
            <h6 class="rec-section__title">بيانات الفرع</h6>
            <div class="row">
                <div class="form-group col-md-4">
                    <x-form.input label="اسم الفرع" :value="$branch->name" name="name" placeholder="المغازي" required autofocus/>
                </div>
            </div>
        </section>
    </div>
    <div class="rec-form__foot">
        <a href="{{ route('branches.index') }}" class="rec-btn"><i class="fe fe-x"></i> إلغاء</a>
        <button type="submit" class="rec-btn rec-btn--primary">
            <i class="fe fe-check"></i> {{ $btn_label ?? ($branch->exists ? 'حفظ التعديلات' : 'أضف') }}
        </button>
    </div>
</div>
