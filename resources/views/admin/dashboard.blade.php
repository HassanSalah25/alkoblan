@extends('admin.layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@php
    $statusColors = [
        'pending' => ['bg' => '#fff4e5', 'text' => '#b54708'],
        'confirmed' => ['bg' => '#eaf2fe', 'text' => '#175cd3'],
        'processing' => ['bg' => '#eaf2fe', 'text' => '#175cd3'],
        'completed' => ['bg' => '#e7f8f0', 'text' => '#067647'],
        'cancelled' => ['bg' => '#fef3f2', 'text' => '#b42318'],
        'new' => ['bg' => '#fff4e5', 'text' => '#b54708'],
        'in_progress' => ['bg' => '#eaf2fe', 'text' => '#175cd3'],
        'resolved' => ['bg' => '#e7f8f0', 'text' => '#067647'],
        'closed' => ['bg' => '#f2f4f7', 'text' => '#475467'],
    ];
    $badge = fn ($status) => $statusColors[$status] ?? ['bg' => '#f2f4f7', 'text' => '#475467'];
@endphp

@section('content')

<div class="welcome-banner mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
    <div>
        <h4 class="mb-1">Welcome back, {{ explode(' ', auth()->user()->name ?? 'Admin')[0] }} 👋</h4>
        <p class="mb-0">{{ now()->locale('en')->isoFormat('dddd, DD MMMM YYYY') }} — here's what's happening with AL-KOBLAN today.</p>
    </div>
    <div class="d-flex gap-2 position-relative">
        @can('products.create')
        <a href="{{ route('admin.products.create') }}" class="btn btn-light btn-sm fw-semibold"><i class="bi bi-plus-lg me-1"></i> New Product</a>
        @endcan
        @can('orders.view')
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline-light btn-sm fw-semibold">View Orders</a>
        @endcan
    </div>
</div>

<div class="row g-3 mb-4">
    @php
        $cards = [
            ['label' => 'Revenue (This Month)', 'value' => number_format($stats['revenue_this_month'], 0).' SAR', 'icon' => 'bi-cash-coin', 'bg' => '#e7f8f0', 'fg' => '#12b76a', 'trend' => $stats['revenue_trend']],
            ['label' => 'Total Orders', 'value' => $stats['orders_total'], 'icon' => 'bi-receipt', 'bg' => '#eaf2fe', 'fg' => '#2f7de1', 'sub' => $stats['orders_this_month'].' this month'],
            ['label' => 'Pending Orders', 'value' => $stats['orders_pending'], 'icon' => 'bi-hourglass-split', 'bg' => '#fff4e5', 'fg' => '#f79009'],
            ['label' => 'Active Products', 'value' => $stats['products_active'].' / '.$stats['products_total'], 'icon' => 'bi-box-seam', 'bg' => '#f4ebff', 'fg' => '#7a5af8'],
            ['label' => 'Categories', 'value' => $stats['categories_total'], 'icon' => 'bi-diagram-3', 'bg' => '#eaf2fe', 'fg' => '#2f7de1'],
            ['label' => 'Low Stock Alerts', 'value' => $stats['stock_low'], 'icon' => 'bi-exclamation-triangle', 'bg' => '#fef3f2', 'fg' => '#f04438'],
            ['label' => 'New Inquiries', 'value' => $stats['contact_new'], 'icon' => 'bi-envelope', 'bg' => '#fff4e5', 'fg' => '#f79009'],
            ['label' => 'New Applications', 'value' => $stats['applications_new'], 'icon' => 'bi-person-badge', 'bg' => '#eaf2fe', 'fg' => '#2f7de1'],
        ];
    @endphp
    @foreach ($cards as $card)
        <div class="col-6 col-lg-3">
            <div class="card stat-card h-100">
                <div class="card-body d-flex align-items-start justify-content-between">
                    <div>
                        <div class="stat-label mb-2">{{ $card['label'] }}</div>
                        <div class="stat-value">{{ $card['value'] }}</div>
                        @if(isset($card['sub']))
                            <div class="text-muted small mt-1">{{ $card['sub'] }}</div>
                        @endif
                        @if(isset($card['trend']))
                            <div class="stat-trend mt-1 {{ $card['trend'] >= 0 ? 'text-success' : 'text-danger' }}">
                                <i class="bi {{ $card['trend'] >= 0 ? 'bi-arrow-up-right' : 'bi-arrow-down-right' }}"></i>
                                {{ abs($card['trend']) }}% vs last month
                            </div>
                        @endif
                    </div>
                    <div class="stat-icon" style="background: {{ $card['bg'] }}; color: {{ $card['fg'] }};">
                        <i class="bi {{ $card['icon'] }}"></i>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-8">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Orders &amp; Revenue — Last 14 Days</span>
                <span class="text-muted small fw-normal">SAR</span>
            </div>
            <div class="card-body">
                <canvas id="trendChart" height="90"></canvas>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header">Quick Actions</div>
            <div class="card-body d-flex flex-column gap-2">
                @can('products.create')
                <a href="{{ route('admin.products.create') }}" class="quick-action">
                    <span class="qa-icon"><i class="bi bi-box-seam"></i></span> Add New Product
                </a>
                @endcan
                @can('blogs.create')
                <a href="{{ route('admin.blog-posts.create') }}" class="quick-action">
                    <span class="qa-icon"><i class="bi bi-journal-plus"></i></span> Write Blog Post
                </a>
                @endcan
                @can('events.create')
                <a href="{{ route('admin.events.create') }}" class="quick-action">
                    <span class="qa-icon"><i class="bi bi-calendar-plus"></i></span> Create Event
                </a>
                @endcan
                @can('media.create')
                <a href="{{ route('admin.media.index') }}" class="quick-action">
                    <span class="qa-icon"><i class="bi bi-cloud-upload"></i></span> Upload Media
                </a>
                @endcan
                @can('contact_messages.view')
                <a href="{{ route('admin.contact-messages.index') }}" class="quick-action">
                    <span class="qa-icon"><i class="bi bi-envelope-open"></i></span> Review Inquiries
                </a>
                @endcan
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-lg-7">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Recent Orders</span>
                @can('orders.view')<a href="{{ route('admin.orders.index') }}" class="small fw-semibold text-decoration-none">View all <i class="bi bi-arrow-left"></i></a>@endcan
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Order #</th>
                            <th>Customer</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Placed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td><a href="{{ route('admin.orders.show', $order) }}" class="fw-semibold text-decoration-none">{{ $order->order_number }}</a></td>
                                <td>{{ $order->customer_name }}</td>
                                <td class="fw-semibold">{{ number_format($order->total, 2) }} {{ $order->currency }}</td>
                                <td>
                                    <span class="status-badge" style="background: {{ $badge($order->status)['bg'] }}; color: {{ $badge($order->status)['text'] }};">
                                        {{ str_replace('_', ' ', $order->status) }}
                                    </span>
                                </td>
                                <td class="text-muted small">{{ optional($order->placed_at ?? $order->created_at)->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5">
                                <div class="empty-state"><i class="bi bi-receipt"></i>No orders yet.</div>
                            </td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-5">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span>Recent Contact Messages</span>
                @can('contact_messages.view')<a href="{{ route('admin.contact-messages.index') }}" class="small fw-semibold text-decoration-none">View all <i class="bi bi-arrow-left"></i></a>@endcan
            </div>
            <div class="card-body pt-2">
                @forelse ($recentMessages as $msg)
                    <div class="list-row d-flex align-items-center justify-content-between">
                        <div>
                            <a href="{{ route('admin.contact-messages.show', $msg) }}" class="fw-semibold text-decoration-none text-dark">{{ $msg->name }}</a>
                            <div class="text-muted small">{{ $msg->subject ?: 'General inquiry' }}</div>
                        </div>
                        <span class="status-badge" style="background: {{ $badge($msg->status)['bg'] }}; color: {{ $badge($msg->status)['text'] }};">
                            {{ str_replace('_', ' ', $msg->status) }}
                        </span>
                    </div>
                @empty
                    <div class="empty-state"><i class="bi bi-envelope"></i>No messages yet.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-exclamation-triangle text-warning me-1"></i> Low Stock Products</span>
                @can('products.view')<a href="{{ route('admin.products.index') }}" class="small fw-semibold text-decoration-none">Manage <i class="bi bi-arrow-left"></i></a>@endcan
            </div>
            <div class="card-body pt-2">
                @forelse ($lowStockProducts as $product)
                    <div class="list-row d-flex align-items-center justify-content-between">
                        <div>
                            <a href="{{ route('admin.products.edit', $product) }}" class="fw-semibold text-decoration-none text-dark">{{ $product->name }}</a>
                            <div class="text-muted small">SKU: {{ $product->sku ?: '—' }}</div>
                        </div>
                        <span class="status-badge" style="background:#fef3f2; color:#b42318;">{{ $product->stock_quantity }} left</span>
                    </div>
                @empty
                    <div class="empty-state"><i class="bi bi-check2-circle"></i>All products are well stocked.</div>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <span><i class="bi bi-calendar-event text-primary me-1"></i> Upcoming Events</span>
                @can('events.view')<a href="{{ route('admin.events.index') }}" class="small fw-semibold text-decoration-none">Manage <i class="bi bi-arrow-left"></i></a>@endcan
            </div>
            <div class="card-body pt-2">
                @forelse ($upcomingEvents as $event)
                    <div class="list-row d-flex align-items-center justify-content-between">
                        <div>
                            <a href="{{ route('admin.events.edit', $event) }}" class="fw-semibold text-decoration-none text-dark">{{ $event->title }}</a>
                            <div class="text-muted small">{{ $event->location }}</div>
                        </div>
                        <span class="text-muted small fw-semibold">{{ optional($event->event_date)->format('d M Y') }}</span>
                    </div>
                @empty
                    <div class="empty-state"><i class="bi bi-calendar-x"></i>No upcoming events scheduled.</div>
                @endforelse
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
<script>
    const ctx = document.getElementById('trendChart');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chart['labels']),
            datasets: [
                {
                    type: 'line',
                    label: 'Revenue (SAR)',
                    data: @json($chart['revenue']),
                    borderColor: '#2f7de1',
                    backgroundColor: 'rgba(47,125,225,.08)',
                    tension: .35,
                    yAxisID: 'y1',
                    pointRadius: 3,
                    pointBackgroundColor: '#2f7de1',
                    fill: true,
                },
                {
                    type: 'bar',
                    label: 'Orders',
                    data: @json($chart['orders']),
                    backgroundColor: 'rgba(18,183,106,.55)',
                    borderRadius: 6,
                    yAxisID: 'y',
                    barThickness: 14,
                },
            ],
        },
        options: {
            responsive: true,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { position: 'top', labels: { boxWidth: 10, font: { size: 11 } } } },
            scales: {
                y: { beginAtZero: true, position: 'left', ticks: { precision: 0 }, grid: { drawOnChartArea: false } },
                y1: { beginAtZero: true, position: 'right', grid: { drawOnChartArea: false } },
            },
        },
    });
</script>
@endpush
