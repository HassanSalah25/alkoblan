@extends('admin.layouts.app')

@section('title', 'Contact Messages')
@section('page-title', 'Contact Messages')

@php
$statusBadge = [
    'new' => 'primary',
    'in_progress' => 'warning',
    'resolved' => 'success',
    'closed' => 'secondary',
];
@endphp

@section('content')
<div class="card">
    <div class="card-header bg-white">
        <form method="GET" class="row gy-2 gx-2 align-items-center">
            <div class="col-auto">
                <input type="text" name="q" value="{{ request('q') }}" class="form-control form-control-sm" placeholder="Search name/email/subject...">
            </div>
            <div class="col-auto">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    @foreach (['new', 'in_progress', 'resolved', 'closed'] as $s)
                        <option value="{{ $s }}" @selected(request('status') === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-auto">
                <button class="btn btn-sm btn-outline-secondary">Filter</button>
                <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Received At</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($contactMessages as $message)
                    <tr>
                        <td>{{ $message->name }}</td>
                        <td>{{ $message->email }}</td>
                        <td>{{ $message->subject }}</td>
                        <td><span class="badge bg-{{ $statusBadge[$message->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $message->status)) }}</span></td>
                        <td>{{ $message->created_at?->format('Y-m-d H:i') }}</td>
                        <td class="text-end">
                            @can('contact_messages.view')
                            <a href="{{ route('admin.contact-messages.show', $message) }}" class="btn btn-sm btn-outline-primary"><i class="bi bi-eye"></i></a>
                            @endcan
                            @can('contact_messages.delete')
                            <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}" class="d-inline" onsubmit="return confirm('Delete this message?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">No contact messages found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer bg-white">{{ $contactMessages->links('pagination::bootstrap-5') }}</div>
</div>
@endsection
