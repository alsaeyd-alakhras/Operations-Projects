<x-front-layout>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/records.css') }}">
        <link rel="stylesheet" href="{{ asset('css/home.css') }}">
    @endpush
    <div class="home-page">
        <div class="home-wrap">
            @if ($canYafa || $canKuwaiti)
                <h2 class="home-title">أهلاً {{ auth()->user()->name }}</h2>
                <p class="home-sub">اختر القسم الذي تريد المتابعة فيه</p>
                <div class="home-grid">
                    @if ($canYafa)
                    <a class="home-card" href="{{ route('records.index') }}">
                        <span class="home-card__icon"><i class="fe fe-layers"></i></span>
                        <h3 class="home-card__title">مستشفى يافا — سجلات العمليات</h3>
                        <p class="home-card__desc">متابعة سجلات المرضى والعمليات والموافقات</p>
                        @if (!is_null($yafaCount))
                            <span class="home-card__chip">السجلات الحالية<b>{{ number_format($yafaCount) }}</b></span>
                        @endif
                    </a>
                    @endif
                    @if ($canKuwaiti)
                    <a class="home-card" href="{{ route('kuwaiti-cases.index') }}">
                        <span class="home-card__icon"><i class="fe fe-activity"></i></span>
                        <h3 class="home-card__title">المركز الكويتي — حالات CT</h3>
                        <p class="home-card__desc">متابعة حالات التصوير المقطعي والبحث الاجتماعي</p>
                        @if ($kuwaitiChip)
                            <span class="home-card__chip">{{ $kuwaitiChip['label'] }}<b>{{ number_format($kuwaitiChip['count']) }}</b></span>
                        @endif
                    </a>
                    @endif
                </div>
            @else
                <div class="home-empty">
                    <h3 class="home-title">لا توجد صلاحيات بعد</h3>
                    <p class="mb-0">حسابك غير مرتبط بأي قسم حالياً، يرجى التواصل مع الإدارة لتفعيل الصلاحيات.</p>
                </div>
            @endif
        </div>
    </div>
    @push('scripts')
        <script>
            try { document.documentElement.classList.toggle('rec-dark', localStorage.getItem('mode') === 'dark'); } catch (e) {}
        </script>
    @endpush
</x-front-layout>
