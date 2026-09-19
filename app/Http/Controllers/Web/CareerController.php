<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use App\Models\JobOpening;
use App\Models\Media;
use Illuminate\Http\Request;

class CareerController extends Controller
{
    public function index()
    {
        return view('pages.careers', ['jobs' => JobOpening::open()->latest()->get()]);
    }

    public function show(string $slug)
    {
        $job = JobOpening::where('slug', $slug)->open()->firstOrFail();

        return view('pages.career-detail', ['job' => $job]);
    }

    public function apply(Request $request, string $slug)
    {
        $job = JobOpening::where('slug', $slug)->open()->firstOrFail();

        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150',
            'phone' => 'nullable|string|max:30',
            'cover_letter' => 'nullable|string|max:3000',
            'cv' => 'required|file|mimes:pdf,doc,docx|max:5120',
        ]);

        $file = $request->file('cv');
        $path = $file->store('media/cv', 'public');

        $media = Media::create([
            'disk' => 'public',
            'path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'type' => Media::typeFromMime($file->getClientMimeType()),
            'size' => $file->getSize(),
            'title' => 'CV - '.$data['name'],
        ]);

        JobApplication::create([
            'job_opening_id' => $job->id,
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'cv_media_id' => $media->id,
            'cover_letter' => $data['cover_letter'] ?? null,
            'status' => 'new',
        ]);

        return back()->with('success', 'تم استلام طلب التوظيف بنجاح، سنتواصل معك في حال توافق مؤهلاتك مع الوظيفة.');
    }
}
