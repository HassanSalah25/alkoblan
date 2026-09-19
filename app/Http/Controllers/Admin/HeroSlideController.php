<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HeroSlide;
use Illuminate\Http\Request;

class HeroSlideController extends Controller
{
    public function index(Request $request)
    {
        $heroSlides = HeroSlide::query()
            ->when($request->filled('q'), fn ($q) => $q->where(function ($qq) use ($request) {
                $qq->where('title', 'like', "%{$request->q}%")
                    ->orWhere('tag', 'like', "%{$request->q}%");
            }))
            ->orderBy('sort_order')
            ->paginate(15)
            ->withQueryString();

        return view('admin.hero-slides.index', compact('heroSlides'));
    }

    public function create()
    {
        return view('admin.hero-slides.form', ['heroSlide' => new HeroSlide()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        HeroSlide::create($data);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide created.');
    }

    public function edit(HeroSlide $hero_slide)
    {
        $heroSlide = $hero_slide;

        return view('admin.hero-slides.form', compact('heroSlide'));
    }

    public function update(Request $request, HeroSlide $hero_slide)
    {
        $data = $this->validated($request);
        $hero_slide->update($data);

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide updated.');
    }

    public function destroy(HeroSlide $hero_slide)
    {
        $hero_slide->delete();

        return redirect()->route('admin.hero-slides.index')->with('success', 'Hero slide deleted.');
    }

    public function toggle(HeroSlide $hero_slide)
    {
        $hero_slide->update(['is_active' => ! $hero_slide->is_active]);

        return back()->with('success', 'Status updated.');
    }

    public function updateOrder(Request $request, HeroSlide $hero_slide)
    {
        $request->validate([
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $hero_slide->update(['sort_order' => $request->sort_order]);

        return back()->with('success', 'Order updated.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'tag' => ['nullable', 'string', 'max:255'],
            'tag_ar' => ['nullable', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string'],
            'subtitle_ar' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:255'],
            'button_text_ar' => ['nullable', 'string', 'max:255'],
            'button_url' => ['nullable', 'string', 'max:255'],
            'button2_text' => ['nullable', 'string', 'max:255'],
            'button2_text_ar' => ['nullable', 'string', 'max:255'],
            'button2_url' => ['nullable', 'string', 'max:255'],
            'image_desktop_id' => ['nullable', 'exists:media,id'],
            'image_mobile_id' => ['nullable', 'exists:media,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
