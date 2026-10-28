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
            <h6 class="rec-section__title">بيانات المستخدم</h6>
            <div class="row">
                <div class="form-group col-md-4">
                    <x-form.input label="الاسم" :value="$user->name"  name="name" placeholder="محمد ...." required autofocus/>
                </div>
                <div class="form-group col-md-4">
                    <x-form.input label="اسم المستخدم" :value="$user->username"  name="username" placeholder="username" required/>
                </div>
                <div class="form-group col-md-4">
                    <x-form.input type="email" label="البريد الالكتروني" :value="$user->email"  name="email" placeholder="example@gmail.com"/>
                </div>
                <div class="form-group col-md-4">
                    @if (isset($btn_label))
                    <x-form.input type="password" label="كلمة المرور" name="password" placeholder="****"  />
                    @else
                    <x-form.input type="password" label="كلمة المرور" name="password" placeholder="****" required />
                    @endif
                </div>

                @if (!isset($btn_label))
                <div class="form-group col-md-4">
                    <x-form.input type="password" label="تأكيد كلمة المرور"  name="confirm_password" placeholder="****" required/>
                </div>
                @endif

                <div class="form-group col-md-4">
                    <label for="branch_id">الفرع</label>
                    <select name="branch_id" id="branch_id" class="form-control">
                        <option value="">بدون فرع (مستخدم عادي)</option>
                        @foreach (\App\Models\Branch::orderBy('name')->get() as $branch)
                            <option value="{{ $branch->id }}" @selected(old('branch_id', $user->branch_id) == $branch->id)>{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label for="column_profile_id">ملف الأعمدة</label>
                    <select name="column_profile_id" id="column_profile_id" class="form-control">
                        <option value="">بدون ملف (كل الأعمدة)</option>
                        @foreach (\Illuminate\Support\Facades\Schema::hasTable('column_profiles') ? \App\Models\ColumnProfile::orderBy('name')->get() : [] as $profile)
                            <option value="{{ $profile->id }}" @selected(old('column_profile_id', $user->column_profile_id) == $profile->id)>{{ $profile->name }}{{ $profile->restricted ? ' (مقيّد)' : '' }}</option>
                        @endforeach
                    </select>
                    @error('column_profile_id')<small class="text-danger">{{ $message }}</small>@enderror
                </div>
            </div>
        </section>
        <section class="rec-section">
            <h6 class="rec-section__title">الصلاحيات</h6>
            <div id="user-roles" class="rec-perms">
                @php $userAbilities = $user->roles()->pluck('role_name')->toArray(); @endphp
                @foreach (app('abilities') as $abilities_name => $ability_array)
                    <div class="rec-perm-group">
                        <h6 class="rec-perm-group__title">{{$ability_array['name']}}</h6>
                        @foreach ($ability_array as $ability_name => $ability)
                            @if ($ability_name != 'name')
                            <label class="rec-perm" for="ability-{{$abilities_name . '.' . $ability_name}}">
                                <input type="checkbox" name="abilities[]" id="ability-{{$abilities_name . '.' . $ability_name}}" value="{{$abilities_name . '.' . $ability_name}}" @checked(in_array($abilities_name . '.' . $ability_name, $userAbilities))>
                                {{$ability}}
                            </label>
                            @endif
                        @endforeach
                    </div>
                @endforeach
            </div>
        </section>
    </div>
    <div class="rec-form__foot">
        <a href="{{ route('users.index') }}" class="rec-btn"><i class="fe fe-x"></i> إلغاء</a>
        <button type="submit" class="rec-btn rec-btn--primary">
            <i class="fe fe-check"></i> {{$btn_label ?? ($user->exists ? 'حفظ التعديلات' : 'أضف')}}
        </button>
    </div>
</div>
