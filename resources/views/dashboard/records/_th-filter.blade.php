@php
    // نفس واجهة Excel لكل أعمدة القوائم: بحث + تحديد قيم + «فارغ».
    // list = القيم تأتي من السيرفر، enum/bool = قيم ثابتة معروفة مسبقًا (النزوح، الدخل، العبء، تمت، التوزيع...).
    $isList = in_array($type, ['list', 'enum', 'bool'], true);
    $static = in_array($type, ['enum', 'bool'], true);
    $emptyOption = $emptyOption ?? ($col !== 'done');
    $staticOptions = $type === 'bool' ? ['1' => 'نعم', '0' => 'لا'] : ($options ?? []);
@endphp
<div class="d-flex align-items-center justify-content-between">
    <span>{{ $label }}</span>
    <div class="filter-dropdown">
        <div class="dropdown">
            <button type="button" class="btn btn-filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" data-col="{{ $col }}" aria-label="تصفية {{ $label }}"><i class="fe fe-pocket"></i></button>
            <div class="filterDropdownMenu dropdown-menu dropdown-menu-right p-2" data-col="{{ $col }}" data-type="{{ $isList ? 'list' : $type }}" @if ($static) data-static="1" @endif>
                @if ($isList)
                    <div class="rec-filter-searchrow"><input type="search" class="form-control rec-filter-search" placeholder="ابحث..." autocomplete="off"><button type="button" class="rec-filter-apply" aria-label="تطبيق"><i class="fe fe-check"></i></button></div>
                    <div class="checkbox-list-box">
                        <label><input type="checkbox" class="rec-filter-all"> <span class="rec-filter-all-label">الكل</span></label>
                        <div class="checkbox-list rec-filter-options">
                            @foreach ($staticOptions as $k => $v)
                                <label><input type="checkbox" value="{{ $k }}"> <span>{{ $v }}</span></label>
                            @endforeach
                        </div>
                    </div>
                    <small class="rec-filter-note"></small>
                    <div class="rec-filter-foot">
                        @if ($emptyOption)<label><input type="checkbox" class="rec-filter-empty"> فارغ</label>@else<span></span>@endif
                        <button type="button" class="rec-filter-clear">مسح</button>
                    </div>
                @elseif ($type === 'date')
                    <label>من <input type="date" class="form-control rec-filter-from" id="from_date"></label>
                    <label>إلى <input type="date" class="form-control rec-filter-to" id="to_date"></label>
                    <div class="rec-filter-foot"><button type="button" class="rec-filter-apply" aria-label="تطبيق"><i class="fe fe-check"></i></button><button type="button" class="rec-filter-clear">مسح</button></div>
                @elseif ($type === 'range')
                    <label>من <input type="number" class="form-control rec-filter-from" id="from_age" min="0"></label>
                    <label>إلى <input type="number" class="form-control rec-filter-to" id="to_age" min="0"></label>
                    <div class="rec-filter-foot"><button type="button" class="rec-filter-apply" aria-label="تطبيق"><i class="fe fe-check"></i></button><button type="button" class="rec-filter-clear">مسح</button></div>
                @endif
            </div>
        </div>
    </div>
</div>
