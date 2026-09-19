<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\Attribute;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $attributes = Attribute::query()
            ->withCount('values')
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('name_ar', 'like', "%{$request->q}%");
            }))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.attributes.index', compact('attributes'));
    }

    public function create()
    {
        return view('admin.attributes.form', ['attribute' => new Attribute()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(Attribute::class, $data['name']);
        Attribute::create($data);

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute created.');
    }

    public function edit(Attribute $attribute)
    {
        return view('admin.attributes.form', compact('attribute'));
    }

    public function update(Request $request, Attribute $attribute)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(Attribute::class, $data['name'], $attribute->id);
        $attribute->update($data);

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute updated.');
    }

    public function destroy(Attribute $attribute)
    {
        if ($attribute->values()->exists()) {
            return back()->with('error', 'Cannot delete: remove all values first.');
        }

        $attribute->delete();

        return redirect()->route('admin.attributes.index')->with('success', 'Attribute deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'type' => ['required', 'in:select,color,text'],
        ]);
    }
}
