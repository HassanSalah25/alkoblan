<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\GeneratesSlugs;
use App\Http\Controllers\Controller;
use App\Models\JobOpening;
use Illuminate\Http\Request;

class JobOpeningController extends Controller
{
    use GeneratesSlugs;

    public function index(Request $request)
    {
        $jobOpenings = JobOpening::query()
            ->when($request->filled('q'), fn ($q) => $q->where('title', 'like', "%{$request->q}%"))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.job-openings.index', compact('jobOpenings'));
    }

    public function create()
    {
        return view('admin.job-openings.form', ['jobOpening' => new JobOpening()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(JobOpening::class, $data['title'], null, $request->input('slug'));

        JobOpening::create($data);

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening created.');
    }

    public function edit(JobOpening $jobOpening)
    {
        return view('admin.job-openings.form', compact('jobOpening'));
    }

    public function update(Request $request, JobOpening $jobOpening)
    {
        $data = $this->validated($request);
        $data['slug'] = $this->uniqueSlug(JobOpening::class, $data['title'], $jobOpening->id, $request->input('slug'));

        $jobOpening->update($data);

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening updated.');
    }

    public function destroy(JobOpening $jobOpening)
    {
        $jobOpening->delete();

        return redirect()->route('admin.job-openings.index')->with('success', 'Job opening deleted.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'title_ar' => ['nullable', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255'],
            'department' => ['nullable', 'string', 'max:255'],
            'location' => ['nullable', 'string', 'max:255'],
            'employment_type' => ['required', 'in:full_time,part_time,contract,internship'],
            'description' => ['nullable', 'string'],
            'description_ar' => ['nullable', 'string'],
            'requirements' => ['nullable', 'string'],
            'requirements_ar' => ['nullable', 'string'],
            'benefits' => ['nullable', 'string'],
            'benefits_ar' => ['nullable', 'string'],
            'deadline' => ['nullable', 'date'],
            'status' => ['required', 'in:open,closed'],
        ]);

        return $data;
    }
}
