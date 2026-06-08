<?php

namespace App\Http\Requests\Quality\Training;

use App\Models\Quality\Training\Assessment;
use App\Models\Quality\Training\Enrollment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class StoreAssessmentAttemptRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $enrollment = Enrollment::findOrFail($this->input('enrollment_id'));
        $assessment = Assessment::findOrFail($this->input('assessment_id'));

        // Validar que el usuario actual es el propietario del enrollment
        if ($enrollment->user_id !== Auth::id()) {
            return false;
        }

        // Validar que el enrollment pertenece al tenant actual
        if ($enrollment->team_id !== auth()->user()?->current_team_id) {
            return false;
        }

        // Validar que el assessment es de la lección del módulo del curso del enrollment
        if ($assessment->lesson_id && $assessment->lesson?->module?->course_id !== $enrollment->course_id) {
            return false;
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'assessment_id' => [
                'required',
                'exists:assessments,id',
            ],
            'enrollment_id' => [
                'required',
                'exists:enrollments,id',
            ],
            'answers' => [
                'required',
                'array',
            ],
            'answers.*' => [
                'required',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'assessment_id.required' => 'La evaluación es requerida.',
            'assessment_id.exists' => 'La evaluación no existe.',
            'enrollment_id.required' => 'La matrícula es requerida.',
            'enrollment_id.exists' => 'La matrícula no existe.',
            'answers.required' => 'Las respuestas son requeridas.',
            'answers.array' => 'Las respuestas deben ser un array.',
            'answers.*.required' => 'Todas las preguntas deben tener respuesta.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'assessment_id' => 'evaluación',
            'enrollment_id' => 'matrícula',
            'answers' => 'respuestas',
        ];
    }
}
