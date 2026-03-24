<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceChangeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'requested_clock_in' => ['required', 'date_format:H:i', 'before:requested_clock_out'],
            'requested_clock_out' => ['required', 'date_format:H:i', 'after:requested_clock_in'],
            'requested_break_start' => ['nullable', 'date_format:H:i', 'after:requested_clock_in', 'before:requested_clock_out'],
            'requested_break_end' => ['nullable', 'date_format:H:i', 'after:requested_break_start', 'before:requested_clock_out'],
            'requested_break2_start' => ['nullable', 'date_format:H:i', 'after:requested_break_end', 'before:requested_clock_out'],
            'requested_break2_end' => ['nullable', 'date_format:H:i', 'after:requested_break2_start', 'before:requested_clock_out'],
            'requested_note' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'requested_clock_in.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_in.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out.date_format' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'requested_break_start.date_format' => '休憩時間が不適切な値です',
            'requested_break_start.after' => '休憩時間が不適切な値です',
            'requested_break_start.before' => '休憩時間が不適切な値です',
            'requested_break_end.date_format' => '休憩時間が不適切な値です',
            'requested_break_end.after' => '休憩時間が不適切な値です',
            'requested_break_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'requested_break2_start.date_format' => '休憩時間が不適切な値です',
            'requested_break2_start.after' => '休憩時間が不適切な値です',
            'requested_break2_start.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'requested_break2_end.date_format' => '休憩時間が不適切な値です',
            'requested_break2_end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'requested_break2_end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'requested_note.required' => '備考を記入してください',
        ];
    }
}
