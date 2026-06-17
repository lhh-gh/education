<?php

declare(strict_types=1);

namespace App\Http\Api\Request\Education\Operations;

use App\Http\Common\Request\Traits\NoAuthorizeTrait;
use Hyperf\Validation\Request\FormRequest;

final class TeacherMakeupAttendanceRequest extends FormRequest
{
    use NoAuthorizeTrait;

    public function rules(): array
    {
        return [
            'makeup_record_id' => ['required', 'integer', 'min:1'],
            'attendance_status' => ['required', 'in:present,late,absent,leave'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_status.in' => 'attendance_status has an invalid value',
        ];
    }
}
