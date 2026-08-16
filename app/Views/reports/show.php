<?php
use App\Core\View;
/** @var string $title @var string $type @var array $rows @var array $columns @var array $summary @var ?string $start @var ?string $end @var array $money @var string $period @var string $keyword @var bool $hasDate */

$period  = $period ?? 'today';
$keyword = $keyword ?? '';
$hasDate = $hasDate ?? ($start !== null);

$periodLabels = [
    'today'     => 'Today',
    'yesterday' => 'Yesterday',
    'week'      => 'This Week',
    'month'     => 'This Month',
    'year'      => 'This Year',
    'custom'    => 'Custom Range',
];

$query = [];
if ($keyword !== '') {
    $query['q'] = $keyword;
}
if ($hasDate) {
    $query['period'] = $period;
    if ($period === 'custom') {
        $query['start'] = (string) $start;
        $query['end'] = (string) $end;
    }
}
$exportUrl = static function (string $format) use ($type, $query): string {
    $params = $query;
    $params['format'] = $format;
    return url('reports/export/' . $type) . '?' . http_build_query($params);
};

$subtitle = 'Current snapshot';
if ($hasDate) {
    $subtitle = ($periodLabels[$period] ?? 'Period') . ' · ' . date('M j, Y', strtotime((string) $start));
    if ($start !== $end) {
        $subtitle .= ' – ' . date('M j, Y', strtotime((string) $end));
    }
}

$colCount = count($columns);
$moneyJson = json_encode(array_values($money), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
$columnsJson = json_encode($columns, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
?>
<?= View::partial('components.page_head', [
    'title'    => $title,
    'subtitle' => $subtitle,
    'extraActions' => can('reports.view') ? '<a href="' . url('reports') . '" class="btn btn-soft">All Reports</a>' : '',
]) ?>

<div class="card mb-3 report-filters"><div class="card-body py-3">
    <form method="GET" id="reportFilterForm" class="row g-2 align-items-end" data-report-type="<?= e($type) ?>" data-has-date="<?= $hasDate ? '1' : '0' ?>">
        <div class="col-md-4 col-lg-3">
            <label class="form-label" for="reportSearch">Search</label>
            <div class="input-group">
                <span class="input-group-text" id="reportSearchIcon"><span class="material-symbols-rounded" style="font-size:18px;">search</span></span>
                <input type="search" name="q" id="reportSearch" class="form-control" placeholder="Type to search…" value="<?= e($keyword) ?>" autocomplete="off">
            </div>
        </div>

        <?php if ($hasDate): ?>
            <div class="col-md-4 col-lg-3">
                <label class="form-label" for="reportPeriod">Date Range</label>
                <select name="period" id="reportPeriod" class="form-select">
                    <?php foreach ($periodLabels as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $period === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-6 col-md-2 col-lg-2 report-custom-dates" id="customDates" style="<?= $period === 'custom' ? '' : 'display:none;' ?>">
                <label class="form-label" for="reportStart">From</label>
                <input type="date" name="start" id="reportStart" class="form-control" value="<?= e((string) $start) ?>">
            </div>
            <div class="col-6 col-md-2 col-lg-2 report-custom-dates" id="customDatesEnd" style="<?= $period === 'custom' ? '' : 'display:none;' ?>">
                <label class="form-label" for="reportEnd">To</label>
                <input type="date" name="end" id="reportEnd" class="form-control" value="<?= e((string) $end) ?>">
            </div>
            <div class="col-auto d-grid">
                <button type="submit" class="btn btn-brand"><span class="material-symbols-rounded">filter_alt</span> Apply</button>
            </div>
        <?php endif; ?>

        <div class="col-lg text-lg-end mt-2 mt-lg-0">
            <?php if (can('reports.export')): ?>
                <a href="<?= e($exportUrl('csv')) ?>" class="btn btn-soft btn-sm report-export" data-format="csv"><span class="material-symbols-rounded">description</span> CSV</a>
                <a href="<?= e($exportUrl('excel')) ?>" class="btn btn-soft btn-sm report-export" data-format="excel"><span class="material-symbols-rounded">table_view</span> Excel</a>
                <a href="<?= e($exportUrl('pdf')) ?>" class="btn btn-soft btn-sm report-export" data-format="pdf"><span class="material-symbols-rounded">picture_as_pdf</span> PDF</a>
                <button type="button" onclick="window.print()" class="btn btn-soft btn-sm"><span class="material-symbols-rounded">print</span> Print</button>
            <?php endif; ?>
        </div>
    </form>
</div></div>

<div class="row g-3 mb-1" id="reportSummary">
    <?php foreach ($summary as $label => $value): ?>
        <div class="col-6 col-md-3"><div class="glass p-3"><div class="text-muted-2" style="font-size:.78rem;font-weight:600;"><?= e($label) ?></div><div class="fw-800 report-summary-value" style="font-size:1.2rem;"><?= is_string($value) ? $value : e((string) $value) ?></div></div></div>
    <?php endforeach; ?>
</div>

<div class="card mt-3">
    <div class="table-wrap position-relative">
        <div id="reportLoading" class="report-loading" style="display:none;" aria-hidden="true">
            <div class="spinner-border spinner-border-sm text-danger" role="status"></div>
        </div>
        <table class="nubia" id="reportTable">
            <thead><tr><?php foreach ($columns as $label): ?><th><?= e($label) ?></th><?php endforeach; ?></tr></thead>
            <tbody id="reportTableBody">
            <?php if (!$rows): ?><tr><td colspan="<?= $colCount ?>" class="text-center text-muted-2 py-4">No data for this period</td></tr><?php endif; ?>
            <?php foreach ($rows as $row): ?>
                <tr>
                <?php foreach ($columns as $key => $label): ?>
                    <?php $val = $row[$key] ?? ''; ?>
                    <td class="<?= in_array($key, $money, true) ? 'fw-800' : '' ?>">
                        <?php if (in_array($key, $money, true)): ?><?= money($val) ?>
                        <?php elseif ($key === 'payment_status'): ?><span class="badge-pill <?= $val === 'paid' ? 'badge-success' : ($val === 'partial' ? 'badge-warning' : 'badge-danger') ?>"><?= ucfirst((string) $val) ?></span>
                        <?php elseif (str_contains($key, 'date')): ?><?= $val ? date('M j, Y', strtotime((string) $val)) : '—' ?>
                        <?php else: ?><?= e((string) $val) ?><?php endif; ?>
                    </td>
                <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php
$reportType = e($type);
$pageScript = <<<JS
(function(){
    const form = document.getElementById('reportFilterForm');
    const search = document.getElementById('reportSearch');
    const tbody = document.getElementById('reportTableBody');
    const summaryEl = document.getElementById('reportSummary');
    const loading = document.getElementById('reportLoading');
    const period = document.getElementById('reportPeriod');
    if (!form || !search || !tbody) return;

    const reportType = form.dataset.reportType;
    const hasDate = form.dataset.hasDate === '1';
    const columns = {$columnsJson};
    const moneyKeys = {$moneyJson};
    const colCount = Object.keys(columns).length;
    let timer = null;
    let abort = null;
    let reqId = 0;

    function esc(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
    }

    function fmtDate(val) {
        if (!val) return '—';
        const d = new Date(String(val) + (String(val).length <= 10 ? 'T00:00:00' : ''));
        if (isNaN(d.getTime())) return esc(val);
        return d.toLocaleDateString(undefined, { month: 'short', day: 'numeric', year: 'numeric' });
    }

    function statusBadge(val) {
        const v = String(val || '');
        const cls = v === 'paid' ? 'badge-success' : (v === 'partial' ? 'badge-warning' : 'badge-danger');
        return '<span class="badge-pill ' + cls + '">' + esc(v.charAt(0).toUpperCase() + v.slice(1)) + '</span>';
    }

    function cellHtml(key, val) {
        if (moneyKeys.includes(key)) {
            return '<td class="fw-800">' + (window.Nubia && Nubia.fmtMoney ? Nubia.fmtMoney(val) : esc(val)) + '</td>';
        }
        if (key === 'payment_status') return '<td>' + statusBadge(val) + '</td>';
        if (key.indexOf('date') !== -1) return '<td>' + fmtDate(val) + '</td>';
        return '<td>' + esc(val) + '</td>';
    }

    function renderRows(rows) {
        if (!rows || !rows.length) {
            tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="text-center text-muted-2 py-4">No data for this period</td></tr>';
            return;
        }
        tbody.innerHTML = rows.map(row => {
            let html = '<tr>';
            Object.keys(columns).forEach(key => { html += cellHtml(key, row[key]); });
            return html + '</tr>';
        }).join('');
    }

    function renderSummary(summary) {
        if (!summaryEl || !summary) return;
        summaryEl.innerHTML = Object.keys(summary).map(label =>
            '<div class="col-6 col-md-3"><div class="glass p-3"><div class="text-muted-2" style="font-size:.78rem;font-weight:600;">' +
            esc(label) + '</div><div class="fw-800 report-summary-value" style="font-size:1.2rem;">' +
            (typeof summary[label] === 'string' ? summary[label] : esc(summary[label])) +
            '</div></div></div>'
        ).join('');
    }

    function buildParams(q) {
        const params = new URLSearchParams();
        if (q) params.set('q', q);
        if (hasDate && period) {
            params.set('period', period.value);
            if (period.value === 'custom') {
                const start = document.getElementById('reportStart');
                const end = document.getElementById('reportEnd');
                if (start && start.value) params.set('start', start.value);
                if (end && end.value) params.set('end', end.value);
            }
        }
        return params;
    }

    function updateExports(q) {
        const params = buildParams(q);
        document.querySelectorAll('.report-export').forEach(a => {
            const p = new URLSearchParams(params);
            p.set('format', a.dataset.format);
            a.href = (window.NUBIA_BASE || '') + '/reports/export/' + reportType + '?' + p.toString();
        });
    }

    function updateUrl(q) {
        const params = buildParams(q);
        const qs = params.toString();
        const path = (window.NUBIA_BASE || '') + '/reports/' + reportType + (qs ? '?' + qs : '');
        history.replaceState(null, '', path);
    }

    function setLoading(on) {
        if (loading) loading.style.display = on ? 'flex' : 'none';
        search.classList.toggle('is-searching', on);
    }

    async function liveSearch() {
        const q = search.value.trim();
        const id = ++reqId;
        if (abort) abort.abort();
        abort = new AbortController();
        setLoading(true);
        try {
            const params = buildParams(q);
            const url = (window.NUBIA_BASE || '') + '/reports/' + reportType + (params.toString() ? '?' + params.toString() : '');
            const { ok, data } = await Nubia.ajax(url, { signal: abort.signal });
            if (id !== reqId) return;
            if (ok && data && data.success) {
                renderRows(data.rows || []);
                renderSummary(data.summary || {});
                updateExports(q);
                updateUrl(q);
            }
        } catch (e) {
            if (e && e.name === 'AbortError') return;
        } finally {
            if (id === reqId) setLoading(false);
        }
    }

    search.addEventListener('input', function () {
        clearTimeout(timer);
        timer = setTimeout(liveSearch, 280);
    });

    // Period dropdown (full page reload for date change)
    if (period) {
        const customBlocks = document.querySelectorAll('.report-custom-dates');
        function toggleCustom() {
            const show = period.value === 'custom';
            customBlocks.forEach(el => { el.style.display = show ? '' : 'none'; });
        }
        period.addEventListener('change', function () {
            toggleCustom();
            if (period.value !== 'custom') form.submit();
        });
        toggleCustom();
    }
})();
JS;
?>
