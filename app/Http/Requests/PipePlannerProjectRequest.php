<?php

namespace App\Http\Requests;

use App\Models\PipePlanningProject;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PipePlannerProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $required = $this->isMethod('post') ? 'required' : 'sometimes';
        $length = 'nullable|numeric|min:0|max:100000';

        return [
            'project_type' => [$required, Rule::in(PipePlanningProject::PROJECT_TYPES)],
            'project_status' => [$required, Rule::in(PipePlanningProject::PROJECT_STATUSES)],
            'system_type' => [$required, Rule::in(PipePlanningProject::SYSTEM_TYPES)],
            'scope' => ['sometimes', Rule::in(PipePlanningProject::SCOPES)],
            'fixtures' => 'sometimes|array',
            'fixtures.bathrooms' => 'nullable|integer|min:0|max:200',
            'fixtures.kitchens' => 'nullable|integer|min:0|max:200',
            'fixtures.water_heaters' => 'nullable|integer|min:0|max:200',
            'fixtures.washing_machines' => 'nullable|integer|min:0|max:200',
            'fixtures.outdoor_taps' => 'nullable|integer|min:0|max:200',
            'fixtures.other' => 'nullable|string|max:300',
            'location' => 'nullable|string|max:150',
            'notes' => 'nullable|string|max:2000',
            'unit' => ['sometimes', Rule::in(PipePlanningProject::UNITS)],
            'measurements' => 'sometimes|array',
            'measurements.room' => 'nullable|array',
            'measurements.room.length' => $length,
            'measurements.room.width' => $length,
            'measurements.room.height' => $length,
            'measurements.distances' => 'nullable|array|max:40',
            'measurements.distances.*.id' => 'required|string|max:40|regex:/^[A-Za-z0-9_-]+$/',
            'measurements.distances.*.from' => 'required|string|max:100',
            'measurements.distances.*.to' => 'required|string|max:100',
            'measurements.distances.*.value' => $length,
            'measurements.distances.*.unit' => ['nullable', Rule::in(PipePlanningProject::UNITS)],
            'measurements.water_source' => 'nullable|string|max:300',
            'measurements.heater_location' => 'nullable|string|max:300',
            'measurements.known_diameters' => 'nullable|string|max:300',
            'measurements.elevations' => 'nullable|string|max:300',
            'measurements.details' => 'nullable|string|max:1000',
            'answers' => 'sometimes|array|max:30',
            'answers.*.question_id' => 'required|string|max:40',
            'answers.*.question' => 'required|string|max:400',
            'answers.*.answer' => 'nullable|string|max:1000',
            'waste_allowance_percent' => 'sometimes|nullable|numeric|min:0|max:30',
        ];
    }

    public function messages(): array
    {
        return self::localizedMessages();
    }

    /** Field-agnostic Arabic messages for planner validation (English uses Laravel defaults). */
    public static function localizedMessages(): array
    {
        if (app()->getLocale() !== 'ar') {
            return [];
        }

        return [
            'required' => 'هذا الحقل مطلوب.',
            'accepted' => 'يجب الموافقة على هذا البند للمتابعة.',
            'in' => 'القيمة المختارة غير صالحة.',
            'numeric' => 'يجب إدخال رقم.',
            'integer' => 'يجب إدخال رقم صحيح.',
            'min' => 'القيمة أقل من الحد المسموح.',
            'max' => 'القيمة أو الحجم أكبر من الحد المسموح.',
            'email' => 'يرجى إدخال بريد إلكتروني صحيح.',
            'string' => 'القيمة غير صالحة.',
            'array' => 'القيمة غير صالحة.',
            'regex' => 'القيمة غير صالحة.',
            'image' => 'يجب أن يكون الملف صورة.',
            'mimetypes' => 'صيغة الصورة غير مدعومة. الصيغ المسموحة: JPG وPNG وWEBP.',
            'uploaded' => 'تعذر رفع الملف. قد يكون حجمه أكبر من المسموح.',
        ];
    }
}
