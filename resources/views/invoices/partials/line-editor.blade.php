@php
    // $lines: the invoice's lines (collection) or null for a new invoice. Old input wins after a validation error.
    $byId = collect($lines ?? [])->values();
    $rows = $byId->map(fn ($line, $i) => [
        'description'   => $line->description,
        'quantity'      => (float) $line->quantity,
        'rate_usd'      => (float) $line->rate_usd,
        'is_percentage' => (bool) $line->is_percentage,
        'applies_to'    => collect($line->applies_to ?? [])->map(fn ($id) => $byId->search(fn ($l) => $l->id === $id))->filter(fn ($i) => $i !== false)->values()->all(),
    ])->all();
    if ($rows === []) {
        $rows = [['description' => $defaultDescription ?? '', 'quantity' => 1, 'rate_usd' => '', 'is_percentage' => false, 'applies_to' => []]];
    }
    $rows = array_values(old('lines', $rows));
    $lineErrors = collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'lines.'))->map(fn ($m) => $m[0])->all();
@endphp

<fieldset class="space-y-3" id="lineEditor">
    <legend class="block text-sm font-medium text-gray-700">
        Lines <span class="text-red-600" aria-hidden="true">*</span>
    </legend>
    <p class="text-xs text-gray-500">Quantity times rate makes each line. A percentage line applies to the lines you pick.</p>
    @error('lines')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

    <div id="lineRows" class="space-y-3"></div>

    <div class="flex flex-wrap items-center gap-4">
        <button type="button" id="addLine"
                class="inline-flex h-10 items-center rounded-md border border-gray-300 bg-white px-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Add line
        </button>
        <div class="ml-auto">
            <label for="amount_usd" class="block text-sm font-medium text-gray-700">Total (USD)</label>
            <input type="number" step="0.01" id="amount_usd" readonly value="{{ old('amount_usd', isset($invoice) ? $invoice->amount_usd : '') }}"
                   class="mt-1 block w-40 rounded-md border-gray-300 bg-gray-50 text-right font-semibold shadow-sm"/>
        </div>
    </div>
</fieldset>

<template id="lineRowTemplate">
    <div class="line-row rounded-md border border-gray-200 p-3" role="group">
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
            <div class="sm:col-span-5">
                <label class="block text-xs font-medium text-gray-700">Description</label>
                <input type="text" data-field="description" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                <p class="mt-1 text-sm text-red-600" data-error="description"></p>
            </div>
            <div class="sm:col-span-2" data-quantity-wrap>
                <label class="block text-xs font-medium text-gray-700">Qty</label>
                <input type="number" step="any" data-field="quantity" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                <p class="mt-1 text-sm text-red-600" data-error="quantity"></p>
            </div>
            <div class="sm:col-span-2">
                <label class="block text-xs font-medium text-gray-700" data-rate-label>Rate (USD)</label>
                <input type="number" step="any" data-field="rate_usd" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                <p class="mt-1 text-sm text-red-600" data-error="rate_usd"></p>
            </div>
            <div class="sm:col-span-3 sm:text-right">
                <span class="block text-xs font-medium text-gray-700">Line total</span>
                <output class="mt-1 block py-2 text-sm font-semibold" data-line-total>0.00</output>
            </div>
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" data-field="is_percentage" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"/>
                Percentage of other lines
            </label>
            <span class="ml-auto inline-flex gap-1">
                <button type="button" data-action="up" class="h-10 min-w-10 rounded border border-gray-300 px-3 text-xs hover:bg-gray-50" aria-label="Move line up">↑</button>
                <button type="button" data-action="down" class="h-10 min-w-10 rounded border border-gray-300 px-3 text-xs hover:bg-gray-50" aria-label="Move line down">↓</button>
                <button type="button" data-action="remove" class="h-10 min-w-10 rounded border border-gray-300 px-3 text-xs text-red-700 hover:bg-red-50">Remove</button>
            </span>
        </div>
        <fieldset class="mt-2 hidden" data-targets>
            <legend class="text-xs font-medium text-gray-700">Applies to</legend>
            <div class="mt-1 flex flex-wrap gap-3" data-target-list></div>
            <p class="mt-1 text-sm text-red-600" data-error="applies_to"></p>
        </fieldset>
    </div>
</template>

<script type="application/json" id="lineEditorData">@json(['rows' => $rows, 'errors' => $lineErrors])</script>
<script>
(() => {
    const data = JSON.parse(document.getElementById('lineEditorData').textContent);
    const rowsEl = document.getElementById('lineRows');
    const tpl = document.getElementById('lineRowTemplate');
    const totalEl = document.getElementById('amount_usd');
    let errors = data.errors || {};
    const lines = (data.rows || []).map(r => ({
        description: r.description ?? '',
        quantity: r.quantity === '' || r.quantity == null ? '' : Number(r.quantity),
        rate_usd: r.rate_usd === '' || r.rate_usd == null ? '' : Number(r.rate_usd),
        is_percentage: !!(r.is_percentage && r.is_percentage !== '0'),
        applies_to: (r.applies_to || []).map(Number),
    }));

    const amounts = () => {
        const out = [];
        lines.forEach((l, i) => { if (!l.is_percentage) out[i] = (Number(l.quantity) || 0) * (Number(l.rate_usd) || 0); });
        lines.forEach((l, i) => {
            if (l.is_percentage) out[i] = l.applies_to.filter(t => t !== i).reduce((s, t) => s + (out[t] || 0), 0) * (Number(l.rate_usd) || 0) / 100;
        });
        return out;
    };

    const updateTotals = () => {
        const a = amounts();
        rowsEl.querySelectorAll('.line-row').forEach((row, i) => { row.querySelector('[data-line-total]').value = (a[i] || 0).toFixed(2); });
        totalEl.value = a.reduce((s, v) => s + (v || 0), 0).toFixed(2);
        totalEl.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const render = () => {
        rowsEl.innerHTML = '';
        lines.forEach((l, i) => {
            const row = tpl.content.firstElementChild.cloneNode(true);
            row.setAttribute('aria-label', `Line ${i + 1}`);
            row.querySelectorAll('[data-field]').forEach(input => {
                const f = input.dataset.field;
                input.name = f === 'is_percentage' ? `lines[${i}][is_percentage]` : `lines[${i}][${f}]`;
                input.id = `line_${i}_${f}`;
                if (f === 'is_percentage') { input.checked = l.is_percentage; input.value = '1'; }
                else input.value = l[f];
                input.previousElementSibling && (input.previousElementSibling.htmlFor = input.id);
            });
            row.querySelector('[data-quantity-wrap]').classList.toggle('hidden', l.is_percentage);
            row.querySelector('[data-rate-label]').textContent = l.is_percentage ? 'Percent' : 'Rate (USD)';
            const targets = row.querySelector('[data-targets]');
            targets.classList.toggle('hidden', !l.is_percentage);
            if (l.is_percentage) {
                const list = row.querySelector('[data-target-list]');
                lines.forEach((other, j) => {
                    if (j === i) return;
                    const label = document.createElement('label');
                    label.className = 'inline-flex items-center gap-1 text-sm';
                    const cb = document.createElement('input');
                    cb.type = 'checkbox'; cb.name = `lines[${i}][applies_to][]`; cb.value = j; cb.checked = l.applies_to.includes(j);
                    cb.className = 'rounded border-gray-300 text-indigo-600 focus:ring-indigo-500';
                    cb.addEventListener('change', () => { l.applies_to = cb.checked ? [...l.applies_to, j] : l.applies_to.filter(t => t !== j); updateTotals(); });
                    label.append(cb, document.createTextNode(` ${j + 1}. ${other.description || 'Line ' + (j + 1)}`));
                    list.append(label);
                });
            }
            ['description', 'quantity', 'rate_usd', 'applies_to'].forEach(f => {
                const msg = errors[`lines.${i}.${f}`];
                const el = row.querySelector(`[data-error="${f}"]`);
                if (msg) el.textContent = msg; else el.remove();
            });
            row.querySelector('[data-action="up"]').disabled = i === 0;
            row.querySelector('[data-action="down"]').disabled = i === lines.length - 1;
            row.querySelector('[data-action="remove"]').disabled = lines.length === 1;
            rowsEl.append(row);
        });
        updateTotals();
    };

    const remap = (from, to) => lines.forEach(l => { l.applies_to = l.applies_to.map(t => t === from ? to : t === to ? from : t); });

    rowsEl.addEventListener('input', e => {
        const row = e.target.closest('.line-row'); if (!row) return;
        const i = [...rowsEl.children].indexOf(row);
        const f = e.target.dataset.field;
        if (f === 'description' || f === 'quantity' || f === 'rate_usd') { lines[i][f] = e.target.value; updateTotals(); }
    });
    rowsEl.addEventListener('change', e => {
        if (e.target.dataset.field !== 'is_percentage') return;
        const i = [...rowsEl.children].indexOf(e.target.closest('.line-row'));
        lines[i].is_percentage = e.target.checked;
        if (lines[i].is_percentage) lines[i].quantity = 1;
        errors = {};
        render();
        document.getElementById(`line_${i}_rate_usd`)?.focus();
    });
    rowsEl.addEventListener('click', e => {
        const btn = e.target.closest('[data-action]'); if (!btn) return;
        const i = [...rowsEl.children].indexOf(btn.closest('.line-row'));
        errors = {};
        if (btn.dataset.action === 'remove' && lines.length > 1) {
            lines.splice(i, 1);
            lines.forEach(l => { l.applies_to = l.applies_to.filter(t => t !== i).map(t => t > i ? t - 1 : t); });
            render();
            document.getElementById(`line_${Math.min(i, lines.length - 1)}_description`)?.focus();
        } else if (btn.dataset.action === 'up' && i > 0) {
            [lines[i - 1], lines[i]] = [lines[i], lines[i - 1]]; remap(i, i - 1); render();
            focusMover(rowsEl.children[i - 1], 'up');
        } else if (btn.dataset.action === 'down' && i < lines.length - 1) {
            [lines[i + 1], lines[i]] = [lines[i], lines[i + 1]]; remap(i, i + 1); render();
            focusMover(rowsEl.children[i + 1], 'down');
        }
    });
    // Keep focus on the row just moved; fall back when the preferred arrow is disabled at the edge.
    const focusMover = (row, pref) => {
        const b = row.querySelector(`[data-action="${pref}"]`);
        (b.disabled ? row.querySelector(`[data-action="${pref === 'up' ? 'down' : 'up'}"]`) : b).focus();
    };
    document.getElementById('addLine').addEventListener('click', () => {
        lines.push({ description: '', quantity: 1, rate_usd: '', is_percentage: false, applies_to: [] });
        errors = {};
        render();
        document.getElementById(`line_${lines.length - 1}_description`)?.focus();
    });

    render();
    // The page's BTC recalc listens later in the document; fire once more when it is wired.
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', updateTotals);
})();
</script>
