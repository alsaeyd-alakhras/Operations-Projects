@include('layouts.partials.header', ['title' => config('app.name')])
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
<script>
    try { document.documentElement.classList.toggle('rec-dark', localStorage.getItem('mode') === 'dark'); } catch (e) {}
    document.body.classList.add('lg-body');
</script>
<div class="lg-wrap">
    <form class="lg-card" action="{{ route('login') }}" method="POST">
        @csrf
        <img class="lg-logo lg-logo--light" src="{{ asset('img/logo.png') }}" alt="{{ config('app.name') }}">
        <img class="lg-logo lg-logo--dark" src="{{ asset('img/logo-wh.png') }}" alt="" aria-hidden="true">
        <h1 class="lg-title">تسجيل الدخول</h1>
        <p class="lg-sub">أدخل بياناتك للوصول إلى لوحة التحكم</p>

        <div class="lg-field">
            <label for="username">اسم المستخدم</label>
            <div class="lg-input">
                <i class="fe fe-user" aria-hidden="true"></i>
                <input type="text" id="username" name="username" value="{{ old('username') }}"
                    class="@error('username') is-invalid @enderror" placeholder="أدخل اسم المستخدم"
                    autocomplete="username" required autofocus>
            </div>
            @error('username')
                <div class="lg-error" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <div class="lg-field">
            <label for="inputPassword">كلمة المرور</label>
            <div class="lg-input">
                <i class="fe fe-lock" aria-hidden="true"></i>
                <input type="password" id="inputPassword" name="password" class="has-eye @error('password') is-invalid @enderror"
                    placeholder="أدخل كلمة المرور" autocomplete="current-password" required>
                <button type="button" class="lg-eye" id="lg-toggle" aria-label="إظهار كلمة المرور"><i class="fe fe-eye"></i></button>
            </div>
            @error('password')
                <div class="lg-error" role="alert">{{ $message }}</div>
            @enderror
        </div>

        <button class="lg-submit" type="submit">دخول</button>

        <p class="lg-foot">© تم الإنشاء بواسطة <a href="https://www.alsaeyd.tech/ar" target="_blank" rel="noopener">م.السيد الأخرس</a></p>
    </form>
</div>
@push('scripts')
    <script>
        (function () {
            var btn = document.getElementById('lg-toggle'), input = document.getElementById('inputPassword');
            btn.addEventListener('click', function () {
                var show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.querySelector('i').className = show ? 'fe fe-eye-off' : 'fe fe-eye';
                btn.setAttribute('aria-label', show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور');
            });
        })();
    </script>
@endpush
@include('layouts.partials.footer')
