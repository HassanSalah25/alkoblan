<?php

namespace Database\Seeders;

use App\Models\JobOpening;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CareerSeeder extends Seeder
{
    public function run(): void
    {
        $jobs = [
            ['Production Line Supervisor', 'مشرف خط إنتاج', 'Production', 'Riyadh', 'full_time'],
            ['Quality Control Engineer', 'مهندس ضبط جودة', 'Quality', 'Riyadh', 'full_time'],
            ['Sales Representative', 'مندوب مبيعات', 'Sales', 'Jeddah', 'full_time'],
            ['Warehouse Coordinator', 'منسق مستودعات', 'Logistics', 'Dammam', 'full_time'],
        ];

        foreach ($jobs as [$title, $titleAr, $dept, $loc, $type]) {
            JobOpening::query()->updateOrCreate(['slug' => Str::slug($title)], [
                'title' => $title, 'title_ar' => $titleAr,
                'department' => $dept, 'location' => $loc, 'employment_type' => $type,
                'description' => "We are looking for a qualified {$title} to join our {$dept} team.",
                'description_ar' => "نبحث عن {$titleAr} مؤهل للانضمام إلى فريق {$dept}.",
                'requirements' => 'Relevant degree, minimum 2 years experience, strong communication skills.',
                'requirements_ar' => 'شهادة ذات صلة، خبرة لا تقل عن سنتين، مهارات تواصل قوية.',
                'benefits' => 'Competitive salary, medical insurance, annual leave, training opportunities.',
                'benefits_ar' => 'راتب تنافسي، تأمين طبي، إجازة سنوية، فرص تدريبية.',
                'deadline' => now()->addMonths(2),
                'status' => 'open',
            ]);
        }
    }
}
