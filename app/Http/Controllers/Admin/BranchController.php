<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index(Request $request)
    {
        $branches = Branch::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('name', 'like', "%{$request->q}%")
                    ->orWhere('city', 'like', "%{$request->q}%");
            }))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.branches.index', compact('branches'));
    }

    public function create()
    {
        return view('admin.branches.form', ['branch' => new Branch()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Branch::create($data);

        return redirect()->route('admin.branches.index')->with('success', 'Branch created.');
    }

    public function edit(Branch $branch)
    {
        return view('admin.branches.form', compact('branch'));
    }

    public function update(Request $request, Branch $branch)
    {
        $data = $this->validated($request);
        $branch->update($data);

        return redirect()->route('admin.branches.index')->with('success', 'Branch updated.');
    }

    public function destroy(Branch $branch)
    {
        $branch->delete();

        return redirect()->route('admin.branches.index')->with('success', 'Branch deleted.');
    }

    public function toggle(Branch $branch)
    {
        $branch->update(['is_active' => ! $branch->is_active]);

        return back()->with('success', 'Status updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'city_ar' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string'],
            'address_ar' => ['nullable', 'string'],
            'phone' => ['nullable', 'string', 'max:255'],
            'phone_secondary' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'string', 'max:255'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'maps_url' => ['nullable', 'string', 'max:255'],
            'working_hours' => ['nullable', 'string'],
            'working_hours_ar' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
