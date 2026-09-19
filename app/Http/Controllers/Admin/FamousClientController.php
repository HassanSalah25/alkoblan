<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FamousClient;
use Illuminate\Http\Request;

class FamousClientController extends Controller
{
    public function index(Request $request)
    {
        $famousClients = FamousClient::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', "%{$request->q}%"))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.famous-clients.index', compact('famousClients'));
    }

    public function create()
    {
        return view('admin.famous-clients.form', ['famousClient' => new FamousClient()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        FamousClient::create($data);

        return redirect()->route('admin.famous-clients.index')->with('success', 'Famous client created.');
    }

    public function edit(FamousClient $famous_client)
    {
        $famousClient = $famous_client;

        return view('admin.famous-clients.form', compact('famousClient'));
    }

    public function update(Request $request, FamousClient $famous_client)
    {
        $data = $this->validated($request);
        $famous_client->update($data);

        return redirect()->route('admin.famous-clients.index')->with('success', 'Famous client updated.');
    }

    public function destroy(FamousClient $famous_client)
    {
        $famous_client->delete();

        return redirect()->route('admin.famous-clients.index')->with('success', 'Famous client deleted.');
    }

    public function toggle(FamousClient $famous_client)
    {
        $famous_client->update(['is_active' => ! $famous_client->is_active]);

        return back()->with('success', 'Status updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'name_ar' => ['nullable', 'string', 'max:255'],
            'logo_id' => ['nullable', 'exists:media,id'],
            'url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
