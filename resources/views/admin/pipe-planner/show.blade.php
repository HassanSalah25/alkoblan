@extends('admin.layouts.app')

@section('title', 'Pipe Planner '.$project->reference)
@section('page-title', 'Pipe Planner · '.$project->reference)

@section('content')

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card mb-4">
            <div class="card-header bg-white">Project</div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">Customer</dt><dd class="col-sm-8">{{ $project->user ? $project->user->name.' ('.$project->user->email.')' : 'Guest' }}</dd>
                    <dt class="col-sm-4">Type / status</dt><dd class="col-sm-8">{{ $project->project_type }} / {{ $project->project_status }}</dd>
                    <dt class="col-sm-4">System / scope</dt><dd class="col-sm-8">{{ $project->system_type }} / {{ $project->scope }}</dd>
                    <dt class="col-sm-4">Fixtures</dt><dd class="col-sm-8">@foreach (($project->fixtures ?? []) as $k => $v) @if ($v !== null && $v !== '') {{ str_replace('_', ' ', $k) }}: {{ $v }}<br>@endif @endforeach</dd>
                    <dt class="col-sm-4">Location</dt><dd class="col-sm-8">{{ $project->location ?: '—' }}</dd>
                    <dt class="col-sm-4">Notes</dt><dd class="col-sm-8" style="white-space:pre-wrap">{{ $project->notes ?: '—' }}</dd>
                    <dt class="col-sm-4">Unit</dt><dd class="col-sm-8">{{ $project->unit }}</dd>
                    <dt class="col-sm-4">Waste allowance</dt><dd class="col-sm-8">{{ $project->waste_allowance_percent !== null ? $project->waste_allowance_percent.'%' : 'not applied' }}</dd>
                </dl>
                <details class="mt-2"><summary class="small">Measurements &amp; answers (raw)</summary>
                    <pre class="small bg-light p-2 mt-2">{{ json_encode(['measurements' => $project->measurements, 'answers' => $project->answers], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </details>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header bg-white">Photos ({{ $project->images->count() }})</div>
            <div class="card-body d-flex flex-wrap gap-2">
                @forelse ($project->images as $img)
                    <a href="{{ route('admin.pipe-planner.image', $img) }}" target="_blank" title="{{ $img->caption }}">
                        <img src="{{ route('admin.pipe-planner.image', $img) }}" alt="" style="width:110px;height:85px;object-fit:cover;border-radius:6px">
                    </a>
                @empty
                    <span class="text-muted small">No photos.</span>
                @endforelse
            </div>
        </div>

        @foreach ($project->quoteRequests as $q)
            <div class="card mb-4">
                <div class="card-header bg-white">Quotation request · {{ $q->created_at->format('Y-m-d H:i') }}</div>
                <div class="card-body small">
                    {{ $q->name }} · {{ $q->email }} · {{ $q->phone ?: '—' }}<br>
                    Status: {{ $q->status }}
                    @if ($q->contact_message_id) · <a href="{{ route('admin.contact-messages.show', $q->contact_message_id) }}">Open contact message</a>@endif
                </div>
            </div>
        @endforeach

        @can('pipe_planner.delete')
            <form method="POST" action="{{ route('admin.pipe-planner.destroy', $project) }}" onsubmit="return confirm('Delete this project and all its photos permanently?')">
                @csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i> Delete project &amp; photos</button>
            </form>
        @endcan
    </div>

    <div class="col-lg-7">
        @forelse ($project->analyses as $analysis)
            <div class="card mb-4">
                <div class="card-header bg-white d-flex justify-content-between">
                    <span>Analysis v{{ $analysis->version }} · {{ $analysis->model ?: '—' }}</span>
                    <span class="badge bg-{{ $analysis->status === 'completed' ? 'success' : ($analysis->status === 'failed' ? 'danger' : 'warning') }}">{{ $analysis->status }}</span>
                </div>
                <div class="card-body small">
                    <p class="mb-2">
                        Readiness: <strong>{{ $analysis->readiness ?? '—' }}</strong> ·
                        Validation: {{ $analysis->validation_status ?? '—' }} ·
                        Failure: {{ $analysis->failure_category ?? '—' }} ·
                        Duration: {{ $analysis->duration_ms ? round($analysis->duration_ms / 1000, 1).'s' : '—' }} ·
                        Tokens: {{ ($analysis->prompt_tokens ?? 0) + ($analysis->completion_tokens ?? 0) }} ·
                        Cost: {{ $analysis->cost_usd !== null ? '$'.number_format($analysis->cost_usd, 5) : 'n/a' }} ·
                        Attempts: {{ $analysis->attempts }} ·
                        Request ID: <code>{{ $analysis->provider_request_id ?? '—' }}</code>
                    </p>
                    @if ($analysis->validation_errors)
                        <div class="alert alert-warning py-2 small mb-2">
                            @foreach ($analysis->validation_errors as $err) <div>{{ $err }}</div> @endforeach
                        </div>
                    @endif
                    @if ($analysis->safety_flags)
                        <div class="alert alert-danger py-2 small mb-2">
                            @foreach ($analysis->safety_flags as $flag) <div>{{ $flag }}</div> @endforeach
                        </div>
                    @endif
                    @if ($analysis->items->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <thead><tr><th>Category</th><th>Ø mm</th><th>Product</th><th>Qty</th><th>Quantity status</th><th>Match</th></tr></thead>
                                <tbody>
                                @foreach ($analysis->items as $item)
                                    <tr>
                                        <td>{{ $item->category }} {{ $item->water ? '('.$item->water.')' : '' }}</td>
                                        <td>{{ $item->diameter_mm ?? '?' }}{{ $item->secondary_diameter_mm ? '×'.$item->secondary_diameter_mm : '' }}</td>
                                        <td>{{ $item->product?->name ?? '—' }}</td>
                                        <td>{{ $item->quantity !== null ? rtrim(rtrim(number_format($item->quantity, 2), '0'), '.').' '.$item->unit : '—' }}</td>
                                        <td>{{ $item->quantity_status }}</td>
                                        <td><span class="badge bg-{{ $item->match_status === 'matched' ? 'success' : 'warning text-dark' }}">{{ $item->match_status }}</span>
                                            @if (! empty($item->derivation['match_reasons'])) <div class="text-muted">{{ implode(', ', $item->derivation['match_reasons']) }}</div> @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card"><div class="card-body text-muted">No analyses yet.</div></div>
        @endforelse
    </div>
</div>
@endsection
