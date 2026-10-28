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
            // العدد + عدد الصفوف في الصفحة يظهران أسفل الجدول بجانب معلومات الصفحة، والتنقل بين الصفحات في الجهة المقابلة
            $('.rec-foot-meta').remove();
            var $meta = $('<div class="rec-foot-meta"></div>')
                .append('<span class="rec-count" id="nav_count" title="عدد السجلات"><i class="fe fe-database"></i><b>0</b></span>')
                .append('<label class="rec-pagelen" for="rec-page-length">صفوف/صفحة</label><select id="rec-page-length" class="rec-select" title="عدد الصفوف في الصفحة" aria-label="عدد الصفوف في الصفحة"><option value="50">50</option><option value="100">100</option><option value="200">200</option><option value="500">500</option></select>');
            $meta.find('#rec-page-length').val(String(table.page.len()));
            function placeMeta() {
                if ($table.closest('.dt-container').find('.rec-foot-meta').length) return;
                $table.closest('.dt-container').find('.dt-layout-row:not(.dt-layout-table) .dt-layout-start').first().prepend($meta);
            }
            placeMeta();
            $table.on('init.dt draw.dt', placeMeta);
            // ===== إعدادات الأعمدة (إظهار/إخفاء) =====
            var storageKey = config.storageKey || ('rec-cols:' + (config.userId || 0) + ':' + (config.archived ? 'archived' : 'index'));
            var lockedCols = ['actions', 'name'];
            var financialCols = config.financialColumns || [];
            var allowedCols = $.isArray(config.allowedColumns) ? config.allowedColumns : null;
            var colNames = config.columns.map(function (c) { return c.name; });
            function isAvailable(name) {
                if (name === 'actions') return true;
                if (!config.canFinancial && financialCols.indexOf(name) !== -1) return false;
                if (allowedCols && allowedCols.indexOf(name) === -1) return false;
                return true;
            }
            function toggleable(name) { return lockedCols.indexOf(name) === -1 && isAvailable(name); }
            function defaultHidden() { return (config.defaultHidden || []).slice(); }
            function readHidden() {
                try {
                    var raw = window.localStorage.getItem(storageKey);
                    if (raw !== null) { var saved = JSON.parse(raw); if ($.isArray(saved)) return saved; }
                } catch (e) { /* التخزين غير متاح */ }
                return defaultHidden();
            }
            function writeHidden(hidden) { try { window.localStorage.setItem(storageKey, JSON.stringify(hidden)); } catch (e) { /* ignore */ } }
            function updateFiller() {
                var count = table.columns().visible().toArray().filter(Boolean).length;
                $table.children('tbody').children('.rec-filler').children('td').attr('colspan', count);
            }
            function applyVisibility(hidden) {
                colNames.forEach(function (name) {
                    var show = isAvailable(name) && (lockedCols.indexOf(name) !== -1 || hidden.indexOf(name) === -1);
                    table.column(name + ':name').visible(show, false);
                });
                updateFiller();
            }
            // عناوين الأعمدة تُقرأ قبل الإخفاء: header() لعمود مخفي يرجع undefined ويكسر باقي التهيئة
            var colLabels = {};
            var $ths = $table.find('thead th');
            colNames.forEach(function (name, i) {
                var th = $ths.eq(i);
                colLabels[name] = $.trim($(th).find('.d-flex > span').first().text()) || $(th).data('label') || $.trim($(th).text()) || name;
            });
            var hiddenCols = readHidden();
            applyVisibility(hiddenCols);
            (function buildColumnsMenu() {
                var $anchor = $('#records-search-reset').closest('li');
                if (!$anchor.length) return;
                var $list = $('<div class="rec-cols-list"></div>');
                colNames.filter(toggleable).forEach(function (name) {
                    var label = colLabels[name];
                    $('<label>').append($('<input>', { type: 'checkbox', 'data-col': name }))
                        .append($('<span>').text(label)).appendTo($list);
                });
                var $item = $('<li class="nav-item dropdown mx-1 d-flex align-items-center rec-cols"></li>')
                    .append('<button class="rec-tool" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="اختيار الأعمدة الظاهرة" aria-label="الأعمدة"><i class="fe fe-columns"></i><span class="ml-1">الأعمدة</span></button>')
                    .append($('<div class="dropdown-menu dropdown-menu-right rec-menu rec-cols-menu"></div>')
                        .append('<div class="rec-cols-head"><button type="button" class="rec-cols-showall">عرض الكل</button><button type="button" class="rec-cols-default">الافتراضي</button></div>')
                        .append($list));
                $item.insertBefore($anchor);
                function syncChecks() { $item.find('input[data-col]').each(function () { $(this).prop('checked', hiddenCols.indexOf($(this).data('col')) === -1); }); }
                function commit() { applyVisibility(hiddenCols); writeHidden(hiddenCols); syncChecks(); }
                syncChecks();
                $item.on('click', '.rec-cols-menu', function (event) { event.stopPropagation(); });
                $item.on('change', 'input[data-col]', function () {
                    var name = String($(this).data('col')), i = hiddenCols.indexOf(name);
                    if (this.checked && i !== -1) hiddenCols.splice(i, 1);
                    if (!this.checked && i === -1) hiddenCols.push(name);
                    commit();
                });
                $item.on('click', '.rec-cols-showall', function () { hiddenCols = []; commit(); });
                $item.on('click', '.rec-cols-default', function () { hiddenCols = defaultHidden(); commit(); });
            }());
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
                // صف حشو يمتص المساحة الفارغة: الجدول يملأ الحاوية (المجاميع بالأسفل) بدون تمطيط صفوف البيانات (الأعمدة المخفية مستبعدة)
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
            RecordsTable.table = table;
            return table;
        }
    };
    // ===== إشعار صغير =====
    function esc(text) { return $('<div>').text(text === null || text === undefined ? '' : text).html(); }
    RecordsTable.toast = function (message, type) {
        var $box = $('#rec-toasts');
        if (!$box.length) $box = $('<div id="rec-toasts" class="rec-toasts" role="status" aria-live="polite"></div>').appendTo('body');
        var $t = $('<div class="rec-toast"></div>').addClass(type === 'error' ? 'rec-toast--error' : 'rec-toast--ok').text(message).appendTo($box);
        setTimeout(function () { $t.fadeOut(250, function () { $t.remove(); }); }, 2600);
    };

    // ===== عمود موافقة الإدارة =====
    // opts: { canManage: bool, url: '.../records/:id/management', csrf: '...' }
    var MGMT = {
        '': { label: 'لم يحدد', cls: 'gray' },
        pending: { label: 'قيد الانتظار', cls: 'amber' },
        approved: { label: 'موافق', cls: 'green' },
        rejected: { label: 'مرفوض', cls: 'red' }
    };
    RecordsTable.managementColumn = function (opts) {
        function rowId(row) { return row.id !== undefined && row.id !== null ? row.id : (row.edit || row.archived || row.delete); }
        function info(row) { var key = row.management_status || ''; return $.extend({ key: key }, MGMT[key] || MGMT['']); }
        function html(row) {
            var st = info(row), label = row.management_status_label || st.label;
            if (!opts.canManage) return '<span class="rec-chip rec-chip--' + st.cls + '">' + esc(label) + '</span>';
            var options = Object.keys(MGMT).map(function (key) {
                return '<option value="' + key + '"' + (key === st.key ? ' selected' : '') + '>' + MGMT[key].label + '</option>';
            }).join('');
            var hasNotes = $.trim(row.management_notes || '') !== '';
            return '<span class="rec-mgmt"><select class="rec-mgmt-select rec-chip rec-chip--' + st.cls + '" data-id="' + esc(rowId(row)) + '" aria-label="حالة موافقة الإدارة">' + options + '</select>' +
                '<button type="button" class="rec-mgmt-note' + (hasNotes ? ' has-notes' : '') + '" data-id="' + esc(rowId(row)) + '" title="' + (hasNotes ? esc(row.management_notes) : 'ملاحظات الإدارة') + '" aria-label="ملاحظات الإدارة"><i class="fe fe-message-square"></i></button></span>';
        }
        function ctx($el) {
            var $td = $el.closest('td'), row = RecordsTable.table.row($el.closest('tr'));
            return { $td: $td, data: row.data() };
        }
        function save(c, status, notes, onFail) {
            var d = c.data, id = rowId(d);
            return $.ajax({ url: opts.url.replace(':id', id), method: 'POST', dataType: 'json', headers: { 'X-CSRF-TOKEN': opts.csrf }, data: { management_status: status, management_notes: notes } })
                .done(function (res) {
                    res = res || {};
                    d.management_status = (res.status !== undefined ? res.status : status) || null;
                    d.management_status_label = res.status_label || MGMT[d.management_status || ''].label;
                    d.management_notes = res.notes !== undefined ? res.notes : notes;
                    c.$td.html(html(d));
                    RecordsTable.toast('تم حفظ موافقة الإدارة');
                })
                .fail(function () { if (onFail) onFail(); RecordsTable.toast('تعذر حفظ موافقة الإدارة', 'error'); });
        }
        var $pop = null, popCtx = null;
        function closePop() { if ($pop) $pop.remove(); $pop = null; popCtx = null; }
        if (opts.canManage) {
            $(document).off('.recmgmt')
                .on('change.recmgmt', '.rec-mgmt-select', function () {
                    var $s = $(this), c = ctx($s), prev = c.data.management_status || '';
                    $s.prop('disabled', true);
                    save(c, $s.val(), c.data.management_notes || '', function () { $s.val(prev); }).always(function () { $s.prop('disabled', false); });
                })
                .on('click.recmgmt', '.rec-mgmt-note', function (event) {
                    var $b = $(this), same = popCtx && popCtx.$btn[0] === this;
                    closePop();
                    if (same) return;
                    popCtx = $.extend(ctx($b), { $btn: $b });
                    $pop = $('<div class="rec-mgmt-pop" dir="rtl"><label>ملاحظات الإدارة</label><textarea rows="4" maxlength="2000"></textarea>' +
                        '<div class="rec-mgmt-pop-foot"><button type="button" class="rec-mgmt-cancel">إلغاء</button><button type="button" class="rec-mgmt-save">حفظ</button></div></div>').appendTo('body');
                    $pop.find('textarea').val(popCtx.data.management_notes || '').trigger('focus');
                    var r = this.getBoundingClientRect(), w = $pop.outerWidth(), h = $pop.outerHeight();
                    var left = Math.max(8, Math.min(window.innerWidth - w - 8, r.left + r.width / 2 - w / 2));
                    var top = r.bottom + 6 + h > window.innerHeight ? Math.max(8, r.top - h - 6) : r.bottom + 6;
                    $pop.css({ left: left, top: top });
                })
                .on('click.recmgmt', '.rec-mgmt-cancel', closePop)
                .on('click.recmgmt', '.rec-mgmt-save', function () {
                    if (!popCtx) return;
                    var c = popCtx, notes = $.trim($pop.find('textarea').val()), $btn = $(this).prop('disabled', true);
                    save(c, c.data.management_status || '', notes).done(closePop).always(function () { $btn.prop('disabled', false); });
                })
                .on('click.recmgmt', function (event) { if (!$(event.target).closest('.rec-mgmt-pop, .rec-mgmt-note').length) closePop(); })
                .on('keydown.recmgmt', function (event) { if (event.key === 'Escape') closePop(); });
        }
        return {
            data: 'management_status', name: 'management_status', orderable: false, searchable: false,
            render: function (data, type, row) {
                return type === 'display' ? html(row) : (row.management_status_label || info(row).label);
            }
        };
    };
}(jQuery));
