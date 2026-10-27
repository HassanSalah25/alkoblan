@extends('admin.layouts.app')

@section('title', 'AI Pipe Planner')
@section('page-title', 'AI Pipe Planner')

@php
$statusBadge = ['draft' => 'secondary', 'queued' => 'info', 'running' => 'warning', 'completed' => 'success', 'failed' => 'danger'];
@endphp

@section('content')

<div class="d-flex justify-content-end mb-3">
    @can('pipe_planner.update')
        <a href="{{ route('admin.pipe-planner.settings') }}" class="btn btn-sm btn-outline-secondary"><i class="bi bi-gear"></i> Settings</a>
    @endcan
</div>

<div class="row g-3 mb-4">
    @foreach ([
        ['Projects', number_format($stats['projects']), 'bi-folder2'],
        ['Analyses (this month)', number_format($stats['analyses_month']), 'bi-cpu'],
        ['Failed (this month)', number_format($stats['failed_month']), 'bi-exclamation-triangle'],
        ['Provider cost (this month)', '$'.number_format($stats['cost_month'], 4), 'bi-currency-dollar'],
        ['Tokens (this month)', number_format($stats['tokens_month']), 'bi-123'],
        ['Quotation requests', number_format($stats['quotes']), 'bi-receipt'],
        ['Catalog-matched items', number_format($stats['matched_items']), 'bi-check2-circle'],
        ['Items needing verification', number_format($stats['unverified_items']), 'bi-question-circle'],
    ] as [$label, $value, $icon])
        <div class="col-6 col-lg-3">
            <div class="card h-100"><div class="card-body">
                <div class="text-muted small"><i class="bi {{ $icon }}"></i> {{ $label }}</div>
                <div class="fs-4 fw-semibold">{{ $value }}</div>
            </div></div>
        </div>
    @endforeach
</div>

<div class="card mb-4">
    <div class="card-header bg-white">
        <form method="GET" class="row gy-2 gx-2 align-items-center">
            <div class="col-auto"><input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Reference (PP-...)"></div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All statuses</option>
                    @foreach (array_keys($statusBadge) as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('admin.pipe-planner.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead><tr><th>Reference</th><th>Customer</th><th>Project</th><th>Photos</th><th>Status</th><th>Latest analysis</th><th>Quote</th><th>Updated</th><th></th></tr></thead>
            <tbody>
            @forelse ($projects as $project)
                <tr>
                    <td><code>{{ $project->reference }}</code></td>
                    <td>{{ $project->user?->name ?? 'Guest' }}</td>
                    <td>{{ str_replace('_', ' ', $project->project_type) }} · {{ $project->system_type }}</td>
                    <td>{{ $project->images_count }}</td>
                    <td><span class="badge bg-{{ $statusBadge[$project->analysis_status] ?? 'secondary' }}">{{ $project->analysis_status }}</span></td>
                    <td>
                        @if ($a = $project->latestAnalysis)
                            v{{ $a->version }} · {{ $a->readiness ? str_replace('_', ' ', $a->readiness) : $a->status }}
                        @else — @endif
                    </td>
                    <td>{{ $project->quote_requests_count ? 'Yes' : '—' }}</td>
                    <td>{{ $project->updated_at->format('Y-m-d H:i') }}</td>
                    <td class="text-end"><a href="{{ route('admin.pipe-planner.show', $project) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                </tr>
            @empty
                <tr><td colspan="9" class="text-center text-muted py-4">No planner projects yet.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $projects->links() }}</div>
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">Recent failed analyses</div>
            <div class="card-body">
                @if ($failureBreakdown->isNotEmpty())
                    <p class="small text-muted mb-2">This month:
                        @foreach ($failureBreakdown as $category => $total)
                            <span class="badge bg-light text-dark">{{ $category ?: 'unknown' }}: {{ $total }}</span>
                        @endforeach
                    </p>
                @endif
                <ul class="list-group list-group-flush">
                    @forelse ($failures as $f)
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span><a href="{{ route('admin.pipe-planner.show', $f->project) }}">{{ $f->project->reference }}</a> v{{ $f->version }} · <code>{{ $f->failure_category }}</code> · {{ $f->model }}</span>
                            <small class="text-muted">{{ $f->created_at->diffForHumans() }}</small>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-muted">No failures.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header bg-white">Recent quotation requests</div>
            <div class="card-body">
                <ul class="list-group list-group-flush">
                    @forelse ($quotes as $q)
                        <li class="list-group-item px-0 d-flex justify-content-between">
                            <span><a href="{{ route('admin.pipe-planner.show', $q->project) }}">{{ $q->project->reference }}</a> · {{ $q->name }} · {{ count($q->items) }} items
                                @if ($q->contact_message_id)
                                    · <a href="{{ route('admin.contact-messages.show', $q->contact_message_id) }}">message</a>
                                @endif
                            </span>
                            <small class="text-muted">{{ $q->created_at->diffForHumans() }}</small>
                        </li>
                    @empty
                        <li class="list-group-item px-0 text-muted">No quotation requests yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
