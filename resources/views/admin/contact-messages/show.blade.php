@extends('admin.layouts.app')

@section('title', 'Contact Message')
@section('page-title', 'Contact Message')

@php
$statusBadge = [
    'new' => 'primary',
    'in_progress' => 'warning',
    'resolved' => 'success',
    'closed' => 'secondary',
];
@endphp

@section('content')
<div class="row">
    <div class="col-lg-7">
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <span>Message Details</span>
                <span class="badge bg-{{ $statusBadge[$contactMessage->status] ?? 'secondary' }}">{{ ucfirst(str_replace('_', ' ', $contactMessage->status)) }}</span>
            </div>
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-sm-3">Name</dt>
                    <dd class="col-sm-9">{{ $contactMessage->name }}</dd>

                    <dt class="col-sm-3">Email</dt>
                    <dd class="col-sm-9">{{ $contactMessage->email }}</dd>

                    <dt class="col-sm-3">Phone</dt>
                    <dd class="col-sm-9">{{ $contactMessage->phone ?: '—' }}</dd>

                    <dt class="col-sm-3">Subject</dt>
                    <dd class="col-sm-9">{{ $contactMessage->subject }}</dd>

                    <dt class="col-sm-3">Received At</dt>
                    <dd class="col-sm-9">{{ $contactMessage->created_at?->format('Y-m-d H:i') }}</dd>

                    <dt class="col-sm-3">Message</dt>
                    <dd class="col-sm-9" style="white-space: pre-wrap;">{{ $contactMessage->message }}</dd>
                </dl>
            </div>
        </div>
    </div>

    <div class="col-lg-5">
        @can('contact_messages.update')
        <div class="card mb-4">
            <div class="card-header bg-white">Update Status &amp; Notes</div>
            <div class="card-body">
                <form method="POST" action="{{ route('admin.contact-messages.update', $contactMessage) }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            @foreach (['new', 'in_progress', 'resolved', 'closed'] as $s)
                                <option value="{{ $s }}" @selected($contactMessage->status === $s)>{{ ucfirst(str_replace('_', ' ', $s)) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Admin Notes</label>
                        <textarea name="admin_notes" rows="6" class="form-control" placeholder="Internal notes / reply summary...">{{ old('admin_notes', $contactMessage->admin_notes) }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary"><i class="bi bi-check-lg"></i> Save Changes</button>
                </form>
            </div>
        </div>
        @endcan

        <div class="d-flex justify-content-between">
            <a href="{{ route('admin.contact-messages.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Back to List</a>
            @can('contact_messages.delete')
            <form method="POST" action="{{ route('admin.contact-messages.destroy', $contactMessage) }}" onsubmit="return confirm('Delete this message?')">
                @csrf
                @method('DELETE')
                <button class="btn btn-outline-danger"><i class="bi bi-trash"></i> Delete</button>
            </form>
            @endcan
        </div>
    </div>
</div>
@endsection
