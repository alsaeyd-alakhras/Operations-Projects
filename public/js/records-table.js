/* Shared, config-driven server-side records table. */
(function ($) {
    'use strict';

    function number(value) {
        return value === null || value === undefined || value === '' || isNaN(value) ? '' :
            Number(value).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    window.RecordsTable = {
        init: function (config) {
            var selectors = config.selectors || {};
            var $table = $(selectors.table || '#records-table');
            var state = { year: 'all', q: '', filters: {}, from_date: '', to_date: '', from_age: '', to_age: '' };
            var jumpToLast = true;
            var requests = {};
            var timers = {};

            function active(col) {
                var filter = state.filters[col];
                return !!(filter && ((filter.values && filter.values.length) || filter.empty || filter.contains));
            }
            // تفريغ قائمة فلتر بدون المساس بقيم الـ checkboxes (الخيارات الثابتة تعتمد على value)
            function resetMenu($menu) {
                $menu.find('input[type="search"], input[type="date"], input[type="number"], select').val('');
                $menu.find('input[type="checkbox"]').prop('checked', false);
                $menu.find('.rec-filter-options label').show();
                $menu.find('.rec-filter-note').text('');
                syncAllLabel($menu);
            }
            function syncAllLabel($menu) {
                $menu.find('.rec-filter-all-label').text($.trim($menu.find('.rec-filter-search').val()) ? 'كل نتائج البحث' : 'الكل');
            }
            // القوائم الثابتة (النزوح، الدخل، تمت...): البحث يتم داخل الصفحة بدون طلب للسيرفر
            function filterStatic($menu) {
                var q = $.trim($menu.find('.rec-filter-search').val()).toLowerCase();
                $menu.find('.rec-filter-options label').each(function () { $(this).toggle($(this).text().toLowerCase().indexOf(q) !== -1); });
            }
            function updateIndicators() {
                $('.btn-filter[data-col]').each(function () {
                    $(this).removeClass('btn-secondary').toggleClass('btn-success', active($(this).data('col')));
                });
                $('.records-search-active').toggleClass('d-none', state.year === 'all' && !state.q);
            }
            function close($menu) {
                $menu.closest('.dropdown').find('[data-toggle="dropdown"]').dropdown('toggle');
            }
            // أي تغيير فلترة/سنة/حجم صفحة: نرجع للصفحة الأولى (resetPaging) ثم نقفز لآخر صفحة بعد وصول العدد الجديد؛
            // بدون ذلك يبقى start القديم (مثلاً 2500) فيرجع السيرفر صفحة فاضية رغم أن النتيجة سجل واحد.
            function reloadLast() { jumpToLast = true; table.ajax.reload(null, true); }
            function reset() {
                state = { year: 'all', q: '', filters: {}, from_date: '', to_date: '', from_age: '', to_age: '' };
                $('#records-year').val('all'); $('#records-q').val('');
                $('.filterDropdownMenu').each(function () { resetMenu($(this)); });
                updateIndicators(); reloadLast();
            }
            function listOptions($menu, values, selected, truncated) {
                var $options = $menu.find('.rec-filter-options').empty();
                var seen = {};
                selected.concat(values).forEach(function (value) {
                    value = String(value);
                    if (seen[value]) return;
                    seen[value] = true;
                    $('<label>').append($('<input>', { type: 'checkbox', value: value, checked: selected.indexOf(value) !== -1 }))
                        .append(document.createTextNode(' ' + value)).appendTo($options);
                });
                $menu.find('.rec-filter-note').text(truncated ? 'تم عرض أول 5000 نتيجة' : '');
            }
            function loadOptions($menu, query) {
                var col = String($menu.data('col'));
                var filters = $.extend({}, state.filters);
                delete filters[col];
                if (requests[col]) requests[col].abort();
                var token = Date.now(); $menu.data('rec-token', token);
                requests[col] = $.getJSON(config.urls.filterOptions, {
                    column: col, archived: config.archived, year: state.year, q: query,
                    filters: JSON.stringify(filters), from_date: state.from_date, to_date: state.to_date,
                    from_age: state.from_age, to_age: state.to_age
                })
                    .done(function (json) {
                        if ($menu.data('rec-token') !== token) return;
                        listOptions($menu, (json && json.values) || [], ((state.filters[col] || {}).values || []).map(String), json && json.truncated);
                    });
            }

            var table = $table.DataTable({
                processing: true, serverSide: true, paging: true, pageLength: 100, lengthMenu: [50, 100, 200, 500],
                searching: false, ordering: false, info: true, autoWidth: false,
                layout: { topStart: null, topEnd: null, bottomStart: 'info', bottomEnd: 'paging' },
                infoCallback: function (settings, start, end, max, total) {
                    var fmt = function (n) { return Number(n).toLocaleString('en-US'); };
                    return total ? 'عرض ' + fmt(start) + ' إلى ' + fmt(end) + ' من ' + fmt(total) : 'لا توجد سجلات';
                },
                language: $.extend({ emptyTable: 'لا توجد سجلات مطابقة', zeroRecords: 'لا توجد سجلات مطابقة' }, { url: config.languageUrl }),
                ajax: { url: config.urls.index, data: function (data) {
                    data.year = state.year; data.q = state.q; data.filters = JSON.stringify(state.filters);
                    data.from_date = state.from_date; data.to_date = state.to_date; data.from_age = state.from_age; data.to_age = state.to_age;
                } },
                columns: config.columns
            });
            (config.financialColumns || []).forEach(function (name) {
                if (!config.canFinancial) table.column(name + ':name').visible(false);
            });
            $table.on('processing.dt', function (_, __, processing) { $table.closest('.rec-card').toggleClass('rec-is-loading', !!processing); });
            $table.on('xhr.dt', function (_, __, json) {
                if (!json) return;
                $('#count_records').text(json.count === undefined ? '' : json.count);
                $('#nav_count b').text(Number(json.recordsFiltered || 0).toLocaleString('en-US'));
                ['amount', 'doctor_share', 'anesthesiologists_share', 'bed', 'private'].forEach(function (key) {
                    if (json.totals && json.totals[key] !== undefined) $('#total_' + key).text(number(json.totals[key]));
                });
            });
            $table.on('draw.dt', function () {
                // صف حشو يمتص المساحة الفارغة: الجدول يملأ الحاوية (المجاميع بالأسفل) بدون تمطيط صفوف البيانات
                var columnsCount = table.columns().visible().toArray().filter(Boolean).length;
                $table.children('tbody').children('.rec-filler').remove();
                $table.children('tbody').append($('<tr class="rec-filler" aria-hidden="true"></tr>').append($('<td>', { colspan: columnsCount })));
                if (jumpToLast) {
                    jumpToLast = false;
                    var info = table.page.info();
                    if (info.pages > 1 && info.page !== info.pages - 1) { table.page('last').draw('page'); return; }
                }
                $table.closest('.dt-layout-table').scrollTop(0);
            });

            $(document).off('.recfilter')
                .on('show.bs.dropdown.recfilter', '.filter-dropdown .dropdown', function () {
                    var $menu = $(this).find('.filterDropdownMenu');
                    if ($menu.data('type') !== 'list') return;
                    var saved = state.filters[String($menu.data('col'))] || {};
                    // إعادة عرض البحث المحفوظ («يحتوي على») عند فتح القائمة من جديد
                    if (saved.contains && !$menu.find('.rec-filter-search').val()) $menu.find('.rec-filter-search').val(saved.contains);
                    $menu.find('.rec-filter-all').prop('checked', !!saved.contains);
                    syncAllLabel($menu);
                    if ($menu.data('static')) filterStatic($menu);
                    else loadOptions($menu, $menu.find('.rec-filter-search').val() || '');
                })
                .on('click.recfilter', '.filterDropdownMenu, .records-search-menu', function (event) { event.stopPropagation(); })
                .on('change.recfilter', '#rec-page-length', function () { table.page.len(parseInt(this.value, 10) || 100); reloadLast(); })
                .on('input.recfilter', '.rec-filter-search', function () {
                    var $menu = $(this).closest('.filterDropdownMenu'), col = $menu.data('col');
                    syncAllLabel($menu);
                    if ($menu.data('static')) { filterStatic($menu); return; }
                    clearTimeout(timers[col]); timers[col] = setTimeout(function () { loadOptions($menu, $menu.find('.rec-filter-search').val()); }, 250);
                })
                .on('keydown.recfilter', '.rec-filter-search', function (event) {
                    if (event.key === 'Enter') { event.preventDefault(); $(this).closest('.filterDropdownMenu').find('.rec-filter-apply').trigger('click'); }
                })
                .on('change.recfilter', '.rec-filter-all', function () { $(this).closest('.filterDropdownMenu').find('.rec-filter-options label:visible input').prop('checked', this.checked); })
                .on('change.recfilter', '.rec-filter-options input', function () { if (!this.checked) $(this).closest('.filterDropdownMenu').find('.rec-filter-all').prop('checked', false); })
                .on('click.recfilter', '.rec-filter-apply', function () {
                    var $menu = $(this).closest('.filterDropdownMenu'), col = String($menu.data('col')), type = $menu.data('type');
                    if (type === 'date' || type === 'range') {
                        state[type === 'date' ? 'from_date' : 'from_age'] = $menu.find('.rec-filter-from').val() || '';
                        state[type === 'date' ? 'to_date' : 'to_age'] = $menu.find('.rec-filter-to').val() || '';
                    } else if (type === 'list') {
                        var values = $menu.find('.rec-filter-options input:checked').map(function () { return this.value; }).get();
                        var empty = $menu.find('.rec-filter-empty').prop('checked') === true;
                        var search = $.trim($menu.find('.rec-filter-search').val()), contains = '';
                        // مثل Excel: نص بحث + «كل نتائج البحث» (أو بدون تحديد) = تصفية «يحتوي على» على كل المطابقات
                        if (!$menu.data('static') && search && ($menu.find('.rec-filter-all').prop('checked') || !values.length)) { contains = search; values = []; }
                        if (values.length || empty || contains) state.filters[col] = { values: values, empty: empty, contains: contains }; else delete state.filters[col];
                    }
                    updateIndicators(); reloadLast(); close($menu);
                })
                .on('click.recfilter', '.rec-filter-clear', function () {
                    var $menu = $(this).closest('.filterDropdownMenu'), col = String($menu.data('col')), type = $menu.data('type');
                    if (type === 'date' || type === 'range') { state[type === 'date' ? 'from_date' : 'from_age'] = ''; state[type === 'date' ? 'to_date' : 'to_age'] = ''; }
                    else delete state.filters[col];
                    resetMenu($menu); updateIndicators(); reloadLast(); close($menu);
                })
                .on('click.recfilter', '#records-search-apply', function () { state.year = $('#records-year').val() || 'all'; state.q = $.trim($('#records-q').val()); updateIndicators(); reloadLast(); close($('.records-search-menu')); })
                .on('keydown.recfilter', '#records-q', function (event) { if (event.key === 'Enter') { event.preventDefault(); $('#records-search-apply').trigger('click'); } })
                .on('click.recfilter', '#records-search-reset', reset)
                .on('click.recfilter', '#refreshData', function () { table.ajax.reload(null, false); })
                .on('click.recfilter', '#excel-export, #print-report', function (event) {
                    event.preventDefault();
                    var isExcel = this.id === 'excel-export', $form = isExcel ? $('#exportExcelForm') : $('#printReport form');
                    $form.find('input[name="data"], input[name="filters"], input[name="archived"], input[name="year"], input[name="q"], input[name="from_date"], input[name="to_date"], input[name="from_age"], input[name="to_age"]').remove();
                    $('<input>', { type: 'hidden', name: 'filters', value: JSON.stringify(state.filters) }).appendTo($form);
                    $('<input>', { type: 'hidden', name: 'archived', value: config.archived }).appendTo($form);
                    $('<input>', { type: 'hidden', name: 'year', value: state.year }).appendTo($form);
                    $('<input>', { type: 'hidden', name: 'q', value: state.q }).appendTo($form);
                    $('<input>', { type: 'hidden', name: 'from_date', value: state.from_date }).appendTo($form);
                    $('<input>', { type: 'hidden', name: 'to_date', value: state.to_date }).appendTo($form);
                    $('<input>', { type: 'hidden', name: 'from_age', value: state.from_age }).appendTo($form);
                    $('<input>', { type: 'hidden', name: 'to_age', value: state.to_age }).appendTo($form);
                    if (isExcel) $form.trigger('submit'); else $('#printReport').modal('show');
                });
            updateIndicators();
            return table;
        }
    };
}(jQuery));
