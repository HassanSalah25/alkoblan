<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public const LOCATIONS = [
        'main' => 'Main',
        'mobile' => 'Mobile',
        'footer_quick' => 'Footer - Quick Links',
        'footer_products' => 'Footer - Products',
        'footer_bottom' => 'Footer - Bottom',
    ];

    public function index(Request $request)
    {
        $groups = [];

        foreach (self::LOCATIONS as $key => $label) {
            $topLevel = MenuItem::where('location', $key)
                ->whereNull('parent_id')
                ->orderBy('sort_order')
                ->get();

            $topLevel->each(function ($item) {
                $item->setRelation('children', MenuItem::where('parent_id', $item->id)->orderBy('sort_order')->get());
            });

            $groups[$key] = [
                'label' => $label,
                'items' => $topLevel,
            ];
        }

        return view('admin.menus.index', compact('groups'));
    }

    public function create()
    {
        $locations = self::LOCATIONS;
        $parents = $this->parentOptions();

        return view('admin.menus.form', ['menu' => new MenuItem(), 'locations' => $locations, 'parents' => $parents]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        MenuItem::create($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menu item created.');
    }

    public function edit(MenuItem $menu)
    {
        $locations = self::LOCATIONS;
        $parents = $this->parentOptions($menu->id);

        return view('admin.menus.form', ['menu' => $menu, 'locations' => $locations, 'parents' => $parents]);
    }

    public function update(Request $request, MenuItem $menu)
    {
        $data = $this->validated($request);
        $menu->update($data);

        return redirect()->route('admin.menus.index')->with('success', 'Menu item updated.');
    }

    public function destroy(MenuItem $menu)
    {
        $menu->delete();

        return redirect()->route('admin.menus.index')->with('success', 'Menu item deleted.');
    }

    public function toggle(MenuItem $menu)
    {
        $menu->update(['is_active' => ! $menu->is_active]);

        return back()->with('success', 'Status updated.');
    }

    private function parentOptions(?int $ignoreId = null)
    {
        return MenuItem::whereNull('parent_id')
            ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
            ->orderBy('location')
            ->orderBy('sort_order')
            ->get();
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'location' => ['required', 'in:'.implode(',', array_keys(self::LOCATIONS))],
            'title' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:255'],
            'open_new_tab' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['open_new_tab'] = $request->boolean('open_new_tab');
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['parent_id'] = $data['parent_id'] ?? null;

        return $data;
    }
}
