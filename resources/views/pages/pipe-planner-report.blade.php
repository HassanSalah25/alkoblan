<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('pp.report.title') }} · {{ $project->reference }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Cairo', sans-serif; color: #2c3e50; margin: 0 auto; max-width: 1000px; padding: 24px; font-size: 14px; line-height: 1.7; background: #fff; }
        header { display: flex; justify-content: space-between; align-items: center; border-bottom: 3px solid #c0392b; padding-bottom: 12px; margin-bottom: 18px; gap: 12px; }
        header img { height: 52px; }
        h1 { font-size: 1.4rem; margin: 0; }
        h2 { font-size: 1.1rem; margin: 24px 0 8px; color: #1a1a2e; border-bottom: 1px solid #e0e0e0; padding-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: start; vertical-align: top; }
        th { background: #f4f6f9; }
        .disclaimer { background: #fff8e6; border: 1px solid #f3d27a; padding: 10px 14px; border-radius: 6px; }
        .muted { color: #7f8c8d; }
        .flag { background: #fdecea; border: 1px solid #f5c6c0; padding: 8px 12px; border-radius: 6px; }
        .print-btn { background: #c0392b; color: #fff; border: 0; padding: 8px 18px; border-radius: 6px; cursor: pointer; font: inherit; }
        @media print { .print-btn { display: none; } body { padding: 0; } }
    </style>
</head>
<body>
@php
    $t = fn ($k, $r = []) => __('pp.'.$k, $r);
    $fmt = fn ($n) => $n === null ? '—' : rtrim(rtrim(number_format((float) $n, 2), '0'), '.');
@endphp
<header>
    <div>
        <h1>{{ $t('report.title') }}</h1>
        <div class="muted">{{ $t('results.reference') }}: <strong>{{ $project->reference }}</strong> · {{ $t('results.version', ['v' => $analysis['version']]) }} · {{ $t('report.generated') }}: {{ now()->format('Y-m-d H:i') }}</div>
    </div>
    <img src="{{ setting('logo') ?: asset('images/logo.png') }}" alt="{{ setting('site_name') }}">
</header>

<button class="print-btn" onclick="window.print()">{{ $t('report.print') }}</button>

<p class="disclaimer"><strong>{{ $t('disclaimer.title') }}</strong> {{ $t('disclaimer.body') }}</p>

<h2>{{ $t('results.summary') }}</h2>
<table>
    <tr><th>{{ $t('details.project_type') }}</th><td>{{ $t('type.'.$project->project_type) }} · {{ $t('pstatus.'.$project->project_status) }}</td></tr>
    <tr><th>{{ $t('details.system_type') }}</th><td>{{ $t('system.'.$project->system_type) }} · {{ $t('scope.'.$project->scope) }}</td></tr>
    <tr><th>{{ $t('results.fixtures') }}</th><td>@foreach (($project->fixtures ?? []) as $k => $v) @if ($v !== null && $v !== '' && $v !== 0) {{ $t('fixture.'.$k) }}: {{ $v }} · @endif @endforeach</td></tr>
    <tr><th>{{ $t('summary.photos') }}</th><td>{{ $project->images()->count() }}</td></tr>
    <tr><th>{{ $t('results.status') }}</th><td><strong>{{ $t('readiness.'.$analysis['readiness']) }}</strong></td></tr>
</table>
@if ($analysis['safety_flags'])
    <p class="flag"><strong>{{ $t('results.safety') }}</strong> {{ implode(' · ', $analysis['safety_flags']) }}</p>
@endif

<h2>{{ $t('results.layout') }}</h2>
<p class="muted">{{ $t('results.layout_note') }}</p>
@if ($opt = $analysis['routing_options'][0] ?? null)
    <p><strong>{{ $opt['title'] }}</strong> — {{ $opt['summary'] }}</p>
    <table>
        <thead><tr><th>{{ $t('measure.from') }}</th><th>{{ $t('measure.to') }}</th><th>{{ $t('bom.category') }}</th><th>{{ $t('bom.diameter') }}</th><th>{{ $t('measure.distance') }}</th><th>{{ $t('bom.verification') }}</th></tr></thead>
        <tbody>
        @php($labels = collect($opt['nodes'])->pluck('label', 'id'))
        @foreach ($opt['segments'] as $s)
            <tr>
                <td>{{ $labels[$s['from']] ?? $s['from'] }}</td>
                <td>{{ $labels[$s['to']] ?? $s['to'] }}</td>
                <td>{{ $t('water.'.$s['water']) }} · {{ $s['line'] }}</td>
                <td>{{ $s['nominal_diameter_mm'] ? $s['nominal_diameter_mm'].' mm' : $t('bom.dia_unknown') }}</td>
                <td>{{ $s['length_m'] !== null && $s['length_source'] !== 'unknown' ? $fmt($s['length_m']).' m'.($s['length_source'] === 'estimated' ? ' ≈' : '') : $t('measure.unknown') }}</td>
                <td>{{ $t('status.'.$s['status']) }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@else
    <p>{{ $t('layout.insufficient') }}</p>
@endif

<h2>{{ $t('results.bom') }}</h2>
<p class="muted">{{ $analysis['waste_allowance_percent'] ? $t('results.waste_applied', ['p' => $analysis['waste_allowance_percent']]) : $t('results.waste_none') }}</p>
<table>
    <thead><tr><th>{{ $t('bom.category') }}</th><th>{{ $t('bom.product') }}</th><th>{{ $t('bom.sku') }}</th><th>{{ $t('bom.diameter') }}</th><th>{{ $t('bom.quantity') }}</th><th>{{ $t('bom.compat') }}</th><th>{{ $t('bom.source') }}</th><th>{{ $t('bom.verification') }}</th><th>{{ $t('bom.notes') }}</th></tr></thead>
    <tbody>
    @forelse ($analysis['items'] as $it)
        <tr>
            <td>{{ $t('cat.'.$it['category']) }}{{ $it['water'] ? ' ('.$t('water.'.$it['water']).')' : '' }}</td>
            <td>{{ $it['product']['name'] ?? $t('bom.requires_verification') }}@if (($it['product']['price'] ?? null) !== null)<br><span class="muted">{{ $fmt($it['product']['price']) }} {{ $it['product']['currency'] }}</span>@endif</td>
            <td>{{ $it['product']['sku'] ?? '—' }}</td>
            <td>{{ $it['diameter_mm'] ? $it['diameter_mm'].($it['secondary_diameter_mm'] ? '×'.$it['secondary_diameter_mm'] : '').' mm' : $t('bom.dia_unknown') }}</td>
            <td>{{ $it['quantity'] !== null ? $fmt($it['quantity']).' '.$t('unit.'.$it['unit']) : $t('bom.not_calculated') }}</td>
            <td>{{ $t('status.'.$it['match_status']) }}</td>
            <td>{{ $t('source.'.$it['source']) }}</td>
            <td>{{ $t('status.'.$it['quantity_status']) }}</td>
            <td>{{ $it['notes'] }}</td>
        </tr>
    @empty
        <tr><td colspan="9">{{ $t('results.no_items') }}</td></tr>
    @endforelse
    </tbody>
</table>
<p class="muted">{{ $t('bom.legend') }}</p>

<h2>{{ $t('results.confidence') }}</h2>
@if ($analysis['observations'])
    <table>
        <thead><tr><th>{{ $t('obs.item') }}</th><th>{{ $t('obs.basis') }}</th><th>{{ $t('obs.confidence') }}</th><th>{{ $t('obs.notes') }}</th></tr></thead>
        <tbody>
        @foreach ($analysis['observations'] as $o)
            <tr><td>{{ $o['label'] }}</td><td>{{ $t('status.'.$o['basis']) }}</td><td>{{ $t('status.conf_'.$o['confidence']) }}</td><td>{{ $o['notes'] }}</td></tr>
        @endforeach
        </tbody>
    </table>
@endif
@foreach (['assumptions' => 'results.assumptions', 'missing_information' => 'results.missing', 'contradictions' => 'results.contradictions', 'corrections' => 'results.corrections'] as $key => $label)
    @if (! empty($analysis[$key]))
        <h3 style="font-size:1rem">{{ $t($label) }}</h3>
        <ul>@foreach ($analysis[$key] as $line)<li>{{ $line }}</li>@endforeach</ul>
    @endif
@endforeach
@if ($analysis['clarification_questions'])
    <h3 style="font-size:1rem">{{ $t('results.questions') }}</h3>
    <ol>@foreach ($analysis['clarification_questions'] as $q)<li>{{ $q['question'] }}</li>@endforeach</ol>
@endif

<p class="muted" style="margin-top:30px">{{ setting('site_name') }} · {{ setting('contact_phone') }} · {{ setting('contact_email') }}</p>
</body>
</html>
