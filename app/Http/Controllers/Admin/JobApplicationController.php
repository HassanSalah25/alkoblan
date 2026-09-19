<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(Request $request)
    {
        $jobApplications = JobApplication::query()
            ->with('job')
            ->when($request->filled('job_opening_id'), fn ($q) => $q->where('job_opening_id', $request->job_opening_id))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        $jobOpenings = JobOpening::all();

        return view('admin.job-applications.index', compact('jobApplications', 'jobOpenings'));
    }

    public function show(JobApplication $job_application)
    {
        $job_application->load('job', 'cv');

        return view('admin.job-applications.show', ['jobApplication' => $job_application]);
    }

    public function update(Request $request, JobApplication $job_application)
    {
        $data = $request->validate([
            'status' => ['required', 'in:new,reviewed,shortlisted,rejected,hired'],
        ]);

        $job_application->update($data);

        return redirect()->route('admin.job-applications.show', $job_application)->with('success', 'Application status updated.');
    }

    public function destroy(JobApplication $job_application)
    {
        $job_application->delete();

        return redirect()->route('admin.job-applications.index')->with('success', 'Application deleted.');
    }
}
