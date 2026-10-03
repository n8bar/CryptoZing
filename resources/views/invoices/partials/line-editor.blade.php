@php
    // $lines: the invoice's lines (collection) or null for a new invoice. Old input wins after a validation error.
    $byId = collect($lines ?? [])->values();
    $rows = $byId->map(fn ($line, $i) => [
        'description' => $line->description,
        'kind'        => $line->kind,
        'quantity'    => (float) $line->quantity,
        'rate_usd'    => (float) $line->rate_usd,
        'applies_to'  => collect($line->applies_to ?? [])->map(fn ($id) => $byId->search(fn ($l) => $l->id === $id))->filter(fn ($i) => $i !== false)->values()->all(),
    ])->all();
    if ($rows === []) {
        $rows = [['description' => $defaultDescription ?? '', 'kind' => 'item', 'quantity' => 1, 'rate_usd' => '', 'applies_to' => []]];
    }
    $rows = array_values(old('lines', $rows));
    $lineErrors = collect($errors->getMessages())->filter(fn ($m, $k) => str_starts_with($k, 'lines.'))->map(fn ($m) => $m[0])->all();
@endphp

<fieldset class="space-y-3" id="lineEditor">
    <legend class="block text-sm font-medium text-gray-700">
        Lines <span class="text-red-600" aria-hidden="true">*</span>
    </legend>
    <p class="text-xs text-gray-500">Quantity times rate makes a line. A subtotal sums the lines above it since the last subtotal. A percentage applies to the lines you pick above it.</p>
    @error('lines')<p class="text-sm text-red-600">{{ $message }}</p>@enderror

    <div id="lineRows" class="space-y-3"></div>

    <div class="flex justify-end">
        <div>
            <label for="amount_usd" class="block text-sm font-medium text-gray-700">Total (USD)</label>
            <input type="number" step="0.01" id="amount_usd" readonly value="{{ old('amount_usd', isset($invoice) ? $invoice->amount_usd : '') }}"
                   class="mt-1 block w-40 rounded-md border-gray-300 bg-gray-50 text-right font-semibold shadow-sm"/>
        </div>
    </div>
</fieldset>

<template id="lineRowTemplate">
    <div class="line-row rounded-md border border-gray-200 p-3" role="group">
        <span class="inline-flex h-6 min-w-6 items-center justify-center rounded-full bg-gray-100 px-2 text-xs font-semibold text-gray-700" data-line-number>1</span>
        <input type="hidden" data-field="kind"/>
        <div class="mt-2 grid grid-cols-1 gap-3 sm:grid-cols-12">
            <div class="sm:col-span-5">
                <label class="block text-xs font-medium text-gray-700">Description</label>
                <input type="text" data-field="description" required
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                <p class="mt-1 text-sm text-red-600" data-error="description"></p>
            </div>
            <div class="sm:col-span-2" data-quantity-wrap>
                <label class="block text-xs font-medium text-gray-700">Qty</label>
                <input type="number" step="any" data-field="quantity"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                <p class="mt-1 text-sm text-red-600" data-error="quantity"></p>
            </div>
            <div class="sm:col-span-2" data-rate-wrap>
                <label class="block text-xs font-medium text-gray-700" data-rate-label>Rate (USD)</label>
                <input type="number" step="any" data-field="rate_usd"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"/>
                <p class="mt-1 text-sm text-red-600" data-error="rate_usd"></p>
            </div>
            <div class="flex items-center justify-between sm:col-span-3 sm:block sm:text-right">
                <div>
                    <span class="block text-xs font-medium text-gray-700">Line total</span>
                    <output class="mt-1 block py-2 text-sm font-semibold" data-line-total>0.00</output>
                </div>
                <span class="inline-flex gap-1 sm:hidden">
                    <button type="button" data-action="up" class="h-10 min-w-10 rounded border border-gray-300 px-3 text-xs hover:bg-gray-50" aria-label="Move line up">↑</button>
                    <button type="button" data-action="down" class="h-10 min-w-10 rounded border border-gray-300 px-3 text-xs hover:bg-gray-50" aria-label="Move line down">↓</button>
                </span>
            </div>
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm">
            <button type="button" data-action="add"
                    class="inline-flex h-10 items-center gap-2 rounded border border-green-300 px-3 text-sm font-semibold text-green-700 hover:bg-green-50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                <span class="text-2xl font-bold leading-none" aria-hidden="true">+</span> Add line
            </button>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" data-kind="percentage" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"/>
                Percentage
            </label>
            <label class="inline-flex items-center gap-2">
                <input type="checkbox" data-kind="subtotal" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"/>
                Subtotal
            </label>
            <span class="ml-auto inline-flex gap-1">
                <button type="button" data-action="up" class="hidden h-10 min-w-10 rounded border border-gray-300 px-3 text-xs hover:bg-gray-50 sm:inline-flex sm:items-center sm:justify-center" aria-label="Move line up">↑</button>
                <button type="button" data-action="down" class="hidden h-10 min-w-10 rounded border border-gray-300 px-3 text-xs hover:bg-gray-50 sm:inline-flex sm:items-center sm:justify-center" aria-label="Move line down">↓</button>
                <button type="button" data-action="remove"
                        class="inline-flex h-10 items-center gap-2 rounded border border-red-300 px-3 text-sm font-semibold text-red-700 hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <span class="text-2xl font-bold leading-none" aria-hidden="true">&times;</span> Remove
                </button>
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
    const KINDS = ['item', 'percentage', 'subtotal'];
    let errors = data.errors || {};
    const lines = (data.rows || []).map(r => ({
        description: r.description ?? '',
        kind: KINDS.includes(r.kind) ? r.kind : 'item',
        quantity: r.quantity === '' || r.quantity == null ? '' : Number(r.quantity),
        rate_usd: r.rate_usd === '' || r.rate_usd == null ? '' : Number(r.rate_usd),
        applies_to: (r.applies_to || []).map(Number),
    }));
    const blank = () => ({ description: '', kind: 'item', quantity: 1, rate_usd: '', applies_to: [] });

    // Amounts in order: a subtotal is the running sum since the last subtotal; a percentage is its picks times its rate.
    const amounts = () => {
        const out = [];
        let running = 0;
        lines.forEach((l, i) => {
            if (l.kind === 'subtotal') out[i] = running;
            else if (l.kind === 'percentage') out[i] = l.applies_to.filter(t => t < i).reduce((s, t) => s + (out[t] || 0), 0) * (Number(l.rate_usd) || 0) / 100;
            else out[i] = (Number(l.quantity) || 0) * (Number(l.rate_usd) || 0);
            running = l.kind === 'subtotal' ? 0 : running + out[i];
        });
        return out;
    };

    const updateTotals = () => {
        const a = amounts();
        rowsEl.querySelectorAll('.line-row').forEach((row, i) => { row.querySelector('[data-line-total]').value = (a[i] || 0).toFixed(2); });
        totalEl.value = a.reduce((s, v, i) => lines[i].kind === 'subtotal' ? s : s + (v || 0), 0).toFixed(2);
        totalEl.dispatchEvent(new Event('input', { bubbles: true }));
    };

    // Default picks for a new percentage line: the subtotals above it, else every line above it.
    const defaultTargets = i => {
        const above = lines.slice(0, i).map((l, j) => [l, j]);
        const subtotals = above.filter(([l]) => l.kind === 'subtotal').map(([, j]) => j);
        return subtotals.length ? subtotals : above.map(([, j]) => j);
    };

    const render = () => {
        rowsEl.innerHTML = '';
        lines.forEach((l, i) => {
            const row = tpl.content.firstElementChild.cloneNode(true);
            row.setAttribute('aria-label', `Line ${i + 1}`);
            row.querySelector('[data-line-number]').textContent = i + 1;
            row.querySelectorAll('[data-field]').forEach(input => {
                const f = input.dataset.field;
                input.name = `lines[${i}][${f}]`;
                input.id = `line_${i}_${f}`;
                input.value = l[f];
                input.previousElementSibling && (input.previousElementSibling.htmlFor = input.id);
            });
            row.querySelectorAll('[data-kind]').forEach(cb => { cb.checked = l.kind === cb.dataset.kind; cb.id = `line_${i}_${cb.dataset.kind}`; });
            const qty = row.querySelector('[data-quantity-wrap]'), rate = row.querySelector('[data-rate-wrap]');
            qty.classList.toggle('hidden', l.kind !== 'item');
            qty.querySelector('input').required = l.kind === 'item';
            rate.classList.toggle('hidden', l.kind === 'subtotal');
            rate.querySelector('input').required = l.kind !== 'subtotal';
            row.querySelector('[data-rate-label]').textContent = l.kind === 'percentage' ? 'Percent' : 'Rate (USD)';
            const targets = row.querySelector('[data-targets]');
            targets.classList.toggle('hidden', l.kind !== 'percentage');
            if (l.kind === 'percentage') {
                const list = row.querySelector('[data-target-list]');
                lines.slice(0, i).forEach((other, j) => {
                    const label = document.createElement('label');
                    label.className = 'inline-flex items-center gap-1 text-sm';
                    const cb = document.createElement('input');
                    cb.type = 'checkbox'; cb.name = `lines[${i}][applies_to][]`; cb.value = j; cb.checked = l.applies_to.includes(j);
                    cb.className = 'rounded border-gray-300 text-indigo-600 focus:ring-indigo-500';
                    cb.addEventListener('change', () => { l.applies_to = cb.checked ? [...l.applies_to, j] : l.applies_to.filter(t => t !== j); updateTotals(); });
                    label.append(cb, document.createTextNode(` ${j + 1}. ${other.description || 'Line ' + (j + 1)}`));
                    list.append(label);
                });
                if (!lines.slice(0, i).length) list.textContent = 'No lines above this one yet.';
            }
            ['description', 'quantity', 'rate_usd', 'applies_to'].forEach(f => {
                const msg = errors[`lines.${i}.${f}`];
                const el = row.querySelector(`[data-error="${f}"]`);
                if (msg) el.textContent = msg; else el.remove();
            });
            row.querySelectorAll('[data-action="up"]').forEach(b => { b.disabled = i === 0; });
            row.querySelectorAll('[data-action="down"]').forEach(b => { b.disabled = i === lines.length - 1; });
            row.querySelector('[data-action="remove"]').disabled = lines.length === 1;
            rowsEl.append(row);
        });
        updateTotals();
    };

    const swap = (from, to) => lines.forEach(l => { l.applies_to = l.applies_to.map(t => t === from ? to : t === to ? from : t); });
    const setKind = (i, kind) => {
        lines[i].kind = kind;
        if (kind !== 'item') lines[i].quantity = 1;
        if (kind === 'subtotal') { lines[i].rate_usd = ''; if (!lines[i].description) lines[i].description = 'Subtotal'; }
        lines[i].applies_to = kind === 'percentage' ? defaultTargets(i) : [];
    };
    // Keep focus on the row just moved; fall back when the preferred arrow is disabled at the edge.
    const visible = (row, action) => [...row.querySelectorAll(`[data-action="${action}"]`)].find(b => b.offsetParent !== null);
    const focusMover = (row, pref) => {
        const b = visible(row, pref);
        (b.disabled ? visible(row, pref === 'up' ? 'down' : 'up') : b)?.focus();
    };

    rowsEl.addEventListener('input', e => {
        const row = e.target.closest('.line-row'); if (!row) return;
        const i = [...rowsEl.children].indexOf(row);
        const f = e.target.dataset.field;
        if (f === 'description' || f === 'quantity' || f === 'rate_usd') { lines[i][f] = e.target.value; updateTotals(); }
    });
    rowsEl.addEventListener('change', e => {
        const kind = e.target.dataset.kind; if (!kind) return;
        const i = [...rowsEl.children].indexOf(e.target.closest('.line-row'));
        setKind(i, e.target.checked ? kind : 'item');
        errors = {};
        render();
        document.getElementById(`line_${i}_${lines[i].kind === 'subtotal' ? 'description' : 'rate_usd'}`)?.focus();
    });
    rowsEl.addEventListener('click', e => {
        const btn = e.target.closest('[data-action]'); if (!btn) return;
        const i = [...rowsEl.children].indexOf(btn.closest('.line-row'));
        errors = {};
        if (btn.dataset.action === 'add') {
            lines.splice(i + 1, 0, blank());
            lines.forEach(l => { l.applies_to = l.applies_to.map(t => t > i ? t + 1 : t); });
            render();
            document.getElementById(`line_${i + 1}_description`)?.focus();
        } else if (btn.dataset.action === 'remove' && lines.length > 1) {
            lines.splice(i, 1);
            lines.forEach(l => { l.applies_to = l.applies_to.filter(t => t !== i).map(t => t > i ? t - 1 : t); });
            render();
            document.getElementById(`line_${Math.min(i, lines.length - 1)}_description`)?.focus();
        } else if (btn.dataset.action === 'up' && i > 0) {
            [lines[i - 1], lines[i]] = [lines[i], lines[i - 1]]; swap(i, i - 1); render();
            focusMover(rowsEl.children[i - 1], 'up');
        } else if (btn.dataset.action === 'down' && i < lines.length - 1) {
            [lines[i + 1], lines[i]] = [lines[i], lines[i + 1]]; swap(i, i + 1); render();
            focusMover(rowsEl.children[i + 1], 'down');
        }
    });

    render();
    // The page's BTC recalc listens later in the document; fire once more when it is wired.
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', updateTotals);
})();
</script>
