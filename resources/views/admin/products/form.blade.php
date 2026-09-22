@extends('admin.layouts.app')

@section('title', $product->exists ? 'Edit Product' : 'New Product')
@section('page-title', $product->exists ? 'Edit Product' : 'New Product')

@section('content')
<form method="POST" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}">
    @csrf
    @if ($product->exists) @method('PUT') @endif

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Basic Information</div>
        <div class="card-body row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Category</label>
                <select name="category_id" class="form-select">
                    <option value="">— None —</option>
                    @foreach ($categories as $c)
                        <option value="{{ $c->id }}" {{ old('category_id', $product->category_id) == $c->id ? 'selected' : '' }}>
                            {{ $c->parent_id ? '— ' : '' }}{{ $c->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">SKU</label>
                <input type="text" name="sku" class="form-control" value="{{ old('sku', $product->sku) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Slug</label>
                <input type="text" name="slug" class="form-control" value="{{ old('slug', $product->slug) }}" placeholder="auto-generated from name if left blank">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Name *</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $product->name) }}" required>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Name (Arabic)</label>
                <input type="text" name="name_ar" class="form-control" value="{{ old('name_ar', $product->name_ar) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Short Description</label>
                <textarea name="short_description" class="form-control" rows="2">{{ old('short_description', $product->short_description) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Short Description (Arabic)</label>
                <textarea name="short_description_ar" class="form-control" rows="2">{{ old('short_description_ar', $product->short_description_ar) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control rich-editor" rows="6">{{ old('description', $product->description) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Description (Arabic)</label>
                <textarea name="description_ar" class="form-control rich-editor" rows="6">{{ old('description_ar', $product->description_ar) }}</textarea>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Pricing &amp; Stock</div>
        <div class="card-body row">
            <div class="col-md-3 mb-3">
                <label class="form-label">Price *</label>
                <input type="number" step="0.01" name="price" class="form-control" value="{{ old('price', $product->price) }}" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Sale Price</label>
                <input type="number" step="0.01" name="sale_price" class="form-control" value="{{ old('sale_price', $product->sale_price) }}">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Currency</label>
                <input type="text" name="currency" class="form-control" value="{{ old('currency', $product->currency ?? 'SAR') }}">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Min Order Qty</label>
                <input type="number" name="min_order_qty" class="form-control" value="{{ old('min_order_qty', $product->min_order_qty ?? 1) }}">
            </div>
            <div class="col-md-2 mb-3">
                <label class="form-label">Sort Order</label>
                <input type="number" name="sort_order" class="form-control" value="{{ old('sort_order', $product->sort_order ?? 0) }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Stock Quantity</label>
                <input type="number" name="stock_quantity" class="form-control" value="{{ old('stock_quantity', $product->stock_quantity ?? 0) }}">
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Stock Status</label>
                <select name="stock_status" class="form-select">
                    @foreach (['in_stock' => 'In Stock', 'out_of_stock' => 'Out of Stock', 'on_backorder' => 'On Backorder'] as $val => $label)
                        <option value="{{ $val }}" {{ old('stock_status', $product->stock_status ?? 'in_stock') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 mb-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_featured" value="1" class="form-check-input" id="is_featured" {{ old('is_featured', $product->is_featured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_featured">Featured</label>
                </div>
            </div>
            <div class="col-md-3 mb-3 d-flex align-items-end">
                <div class="form-check">
                    <input type="checkbox" name="is_active" value="1" class="form-check-input" id="is_active" {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label" for="is_active">Active</label>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
            Technical Specifications
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSpecRow()"><i class="bi bi-plus"></i> Add Row</button>
        </div>
        <div class="card-body">
            <table class="table table-sm" id="specRows">
                <thead><tr><th>Label</th><th>Label (Arabic)</th><th>Value</th><th></th></tr></thead>
                <tbody id="specRowsBody">
                    @php $specs = old('tech_spec_label') ? [] : ($product->technical_specifications ?? []); @endphp
                    @foreach ($specs as $row)
                        <tr>
                            <td><input type="text" name="tech_spec_label[]" class="form-control form-control-sm" value="{{ $row['label'] ?? '' }}"></td>
                            <td><input type="text" name="tech_spec_label_ar[]" class="form-control form-control-sm" value="{{ $row['label_ar'] ?? '' }}"></td>
                            <td><input type="text" name="tech_spec_value[]" class="form-control form-control-sm" value="{{ $row['value'] ?? '' }}"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x"></i></button></td>
                        </tr>
                    @endforeach
                    @if (old('tech_spec_label'))
                        @foreach (old('tech_spec_label') as $i => $label)
                            <tr>
                                <td><input type="text" name="tech_spec_label[]" class="form-control form-control-sm" value="{{ $label }}"></td>
                                <td><input type="text" name="tech_spec_label_ar[]" class="form-control form-control-sm" value="{{ old('tech_spec_label_ar')[$i] ?? '' }}"></td>
                                <td><input type="text" name="tech_spec_value[]" class="form-control form-control-sm" value="{{ old('tech_spec_value')[$i] ?? '' }}"></td>
                                <td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest('tr').remove()"><i class="bi bi-x"></i></button></td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>
            <div class="mt-3">
                <label class="form-label small text-muted">Advanced: raw "specifications" JSON (optional)</label>
                <textarea name="specifications" class="form-control font-monospace small" rows="3">{{ old('specifications', $product->specifications ? json_encode($product->specifications, JSON_PRETTY_PRINT) : '') }}</textarea>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">SEO</div>
        <div class="card-body row">
            <div class="col-md-6 mb-3">
                <label class="form-label">SEO Title</label>
                <input type="text" name="seo_title" class="form-control" value="{{ old('seo_title', $product->seo_title) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">SEO Title (Arabic)</label>
                <input type="text" name="seo_title_ar" class="form-control" value="{{ old('seo_title_ar', $product->seo_title_ar) }}">
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">SEO Description</label>
                <textarea name="seo_description" class="form-control" rows="2">{{ old('seo_description', $product->seo_description) }}</textarea>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">SEO Description (Arabic)</label>
                <textarea name="seo_description_ar" class="form-control" rows="2">{{ old('seo_description_ar', $product->seo_description_ar) }}</textarea>
            </div>
            <div class="col-md-12 mb-3">
                <label class="form-label">SEO Keywords</label>
                <input type="text" name="seo_keywords" class="form-control" value="{{ old('seo_keywords', $product->seo_keywords) }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary">Save Product</button>
    <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary">Back</a>
</form>

@if ($product->exists)
    <hr class="my-4">

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Images</div>
        <div class="card-body">
            <div class="row g-2 mb-3">
                @foreach ($product->images as $img)
                    <div class="col-6 col-md-2 text-center">
                        <img src="{{ $img->media->url }}" class="img-fluid rounded mb-1" style="height:90px;object-fit:cover;width:100%;">
                        <div class="small"><span class="badge {{ $img->type === 'main' ? 'bg-success' : 'bg-secondary' }}">{{ $img->type }}</span></div>
                        <div class="d-flex gap-1 justify-content-center mt-1">
                            @if ($img->type !== 'main')
                                <form method="POST" action="{{ route('admin.product-images.set-main', $img) }}">
                                    @csrf @method('PATCH')
                                    <button class="btn btn-xs btn-outline-primary btn-sm" title="Set as main"><i class="bi bi-star"></i></button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('admin.product-images.destroy', $img) }}" onsubmit="return confirm('Remove this image?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-xs btn-outline-danger btn-sm"><i class="bi bi-trash"></i></button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
            <form method="POST" action="{{ route('admin.products.images.store', $product) }}" class="d-flex align-items-end gap-2 flex-wrap">
                @csrf
                <x-admin.media-picker field="new_image_media_id" type="image" label="Pick Image" />
                <div>
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="gallery">Gallery</option>
                        <option value="main">Main</option>
                        <option value="thumbnail">Thumbnail</option>
                        <option value="technical_drawing">Technical Drawing</option>
                    </select>
                </div>
                <input type="hidden" name="media_id" id="media_id_bridge_image">
                <button type="submit" class="btn btn-sm btn-primary mb-1" onclick="document.getElementById('media_id_bridge_image').value = document.getElementById('input-new_image_media_id').value;">Add Image</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Files (downloads)</div>
        <div class="card-body">
            <table class="table table-sm mb-3">
                <thead><tr><th>Title</th><th>Type</th><th></th><th></th></tr></thead>
                <tbody>
                    @foreach ($product->files as $file)
                        <tr>
                            <td>{{ $file->title ?: $file->media->original_name }}</td>
                            <td><span class="badge bg-light text-dark">{{ $file->type }}</span></td>
                            <td><a href="{{ $file->media->url }}" target="_blank" class="btn btn-sm btn-outline-secondary"><i class="bi bi-download"></i></a></td>
                            <td>
                                <form method="POST" action="{{ route('admin.product-files.destroy', $file) }}" onsubmit="return confirm('Remove this file?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <form method="POST" action="{{ route('admin.products.files.store', $product) }}" class="d-flex align-items-end gap-2 flex-wrap">
                @csrf
                <x-admin.media-picker field="new_file_media_id" label="Pick File" />
                <input type="hidden" name="media_id" id="media_id_bridge_file">
                <div>
                    <label class="form-label small">Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="catalog">Catalog</option>
                        <option value="price_list">Price List</option>
                        <option value="datasheet">Datasheet</option>
                        <option value="installation_guide">Installation Guide</option>
                        <option value="certificate">Certificate</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                <div>
                    <label class="form-label small">Title</label>
                    <input type="text" name="title" class="form-control form-control-sm">
                </div>
                <div>
                    <label class="form-label small">Title (Arabic)</label>
                    <input type="text" name="title_ar" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-primary mb-1" onclick="document.getElementById('media_id_bridge_file').value = document.getElementById('input-new_file_media_id').value;">Attach File</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Filterable Attributes</div>
        <div class="card-body">
            <form method="POST" action="{{ route('admin.products.attributes.sync', $product) }}">
                @csrf @method('PUT')
                @php $assignedIds = $product->attributeValues->pluck('id')->all(); @endphp
                @foreach ($attributes as $attribute)
                    <div class="mb-2">
                        <div class="fw-semibold small">{{ $attribute->name }}</div>
                        <div class="d-flex flex-wrap gap-3">
                            @foreach ($attribute->values as $value)
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="attribute_value_ids[]" value="{{ $value->id }}" id="av-{{ $value->id }}" {{ in_array($value->id, $assignedIds) ? 'checked' : '' }}>
                                    <label class="form-check-label small" for="av-{{ $value->id }}">{{ $value->value }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
                <button type="submit" class="btn btn-sm btn-primary mt-2">Save Attributes</button>
            </form>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-white fw-semibold">Variants</div>
        <div class="card-body">
            @foreach ($product->variants as $variant)
                <form method="POST" action="{{ route('admin.product-variants.update', $variant) }}" class="border rounded p-2 mb-2">
                    @csrf @method('PUT')
                    <div class="row g-2 align-items-end">
                        <div class="col-md-2">
                            <label class="form-label small">SKU</label>
                            <input type="text" name="sku" class="form-control form-control-sm" value="{{ $variant->sku }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Price</label>
                            <input type="number" step="0.01" name="price" class="form-control form-control-sm" value="{{ $variant->price }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Sale Price</label>
                            <input type="number" step="0.01" name="sale_price" class="form-control form-control-sm" value="{{ $variant->sale_price }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small">Stock</label>
                            <input type="number" name="stock_quantity" class="form-control form-control-sm" value="{{ $variant->stock_quantity }}">
                        </div>
                        <div class="col-md-1 form-check">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input" {{ $variant->is_active ? 'checked' : '' }}>
                            <label class="form-check-label small">Active</label>
                        </div>
                        <div class="col-md-3">
                            @php $variantAssigned = $variant->attributeValues->pluck('id')->all(); @endphp
                            @foreach ($attributes as $attribute)
                                @foreach ($attribute->values as $value)
                                    <div class="form-check form-check-inline">
                                        <input type="checkbox" class="form-check-input" name="attribute_value_ids[]" value="{{ $value->id }}" {{ in_array($value->id, $variantAssigned) ? 'checked' : '' }}>
                                        <label class="form-check-label small">{{ $attribute->name }}: {{ $value->value }}</label>
                                    </div>
                                @endforeach
                            @endforeach
                        </div>
                    </div>
                    <div class="mt-2">
                        <button type="submit" class="btn btn-sm btn-outline-primary">Save</button>
                        <button type="submit" formaction="{{ route('admin.product-variants.destroy', $variant) }}" formmethod="POST" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this variant?')">
                            Delete
                        </button>
                    </div>
                </form>
            @endforeach

            <form method="POST" action="{{ route('admin.products.variants.store', $product) }}" class="border rounded p-2 bg-light">
                @csrf
                <div class="row g-2 align-items-end">
                    <div class="col-md-2">
                        <label class="form-label small">SKU</label>
                        <input type="text" name="sku" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Price</label>
                        <input type="number" step="0.01" name="price" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Sale Price</label>
                        <input type="number" step="0.01" name="sale_price" class="form-control form-control-sm">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small">Stock</label>
                        <input type="number" name="stock_quantity" class="form-control form-control-sm" value="0">
                    </div>
                    <div class="col-md-1 form-check">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" checked>
                        <label class="form-check-label small">Active</label>
                    </div>
                    <div class="col-md-3">
                        @foreach ($attributes as $attribute)
                            @foreach ($attribute->values as $value)
                                <div class="form-check form-check-inline">
                                    <input type="checkbox" class="form-check-input" name="attribute_value_ids[]" value="{{ $value->id }}">
                                    <label class="form-check-label small">{{ $attribute->name }}: {{ $value->value }}</label>
                                </div>
                            @endforeach
                        @endforeach
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-primary mt-2"><i class="bi bi-plus"></i> Add Variant</button>
            </form>
        </div>
    </div>
@endif

@push('scripts')
<script>
    function addSpecRow() {
        const tbody = document.getElementById('specRowsBody');
        const tr = document.createElement('tr');
        tr.innerHTML = '<td><input type="text" name="tech_spec_label[]" class="form-control form-control-sm"></td>'
            + '<td><input type="text" name="tech_spec_label_ar[]" class="form-control form-control-sm"></td>'
            + '<td><input type="text" name="tech_spec_value[]" class="form-control form-control-sm"></td>'
            + '<td><button type="button" class="btn btn-sm btn-outline-danger" onclick="this.closest(\'tr\').remove()"><i class="bi bi-x"></i></button></td>';
        tbody.appendChild(tr);
    }
</script>
@include('admin.partials.rich-editor')
@endpush
@endsection
