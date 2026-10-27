@extends('admin.layouts.app')

@section('title', 'AI Pipe Planner Settings')
@section('page-title', 'AI Pipe Planner Settings')

@section('content')

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-body">
                <div class="alert {{ $apiKeyConfigured ? 'alert-success' : 'alert-warning' }} small">
                    OpenRouter API key:
                    <strong>{{ $apiKeyConfigured ? 'configured (server environment)' : 'NOT configured — set OPENROUTER_API_KEY in the server .env' }}</strong>.
                    The key is never stored in or shown by this panel.
                </div>
                <form method="POST" action="{{ route('admin.pipe-planner.settings.update') }}">
                    @csrf @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">OpenRouter model id</label>
                        <input name="model" class="form-control @error('model') is-invalid @enderror" value="{{ old('model', $values['model']) }}" placeholder="{{ $envModel ?: 'e.g. provider/model-name' }}">
                        <div class="form-text">Must be a vision-capable model (accepts image input). Capability is verified against OpenRouter's model list before every analysis. Leave empty to use the OPENROUTER_MODEL env value{{ $envModel ? ' ('.$envModel.')' : '' }}.</div>
                        @error('model')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                    <div class="row">
                        @foreach ([
                            ['max_images', 'Maximum photos per project', 'number'],
                            ['max_image_kb', 'Maximum photo size (KB)', 'number'],
                            ['analyses_per_day', 'Analyses per customer / IP per day', 'number'],
                            ['monthly_budget_usd', 'Monthly provider budget (USD, 0 = no limit)', 'number'],
                            ['max_tokens', 'Max output tokens per analysis', 'number'],
                            ['retention_days', 'Retention for projects without a quote (days)', 'number'],
                        ] as [$key, $label, $type])
                            <div class="col-md-6 mb-3">
                                <label class="form-label">{{ $label }}</label>
                                <input type="{{ $type }}" step="any" name="{{ $key }}" class="form-control @error($key) is-invalid @enderror" value="{{ old($key, $values[$key]) }}" required>
                                @error($key)<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        @endforeach
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="show_prices" name="show_prices" value="1" @checked(old('show_prices', $values['show_prices']))>
                        <label class="form-check-label" for="show_prices">Show catalog prices in planner results (only for matched products with a price)</label>
                    </div>
                    <button class="btn btn-primary">Save settings</button>
                    <a href="{{ route('admin.pipe-planner.index') }}" class="btn btn-outline-secondary">Back</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
