<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceDetailRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $clockIn = $this->input('clock_in');
        $clockOut = $this->input('clock_out');

        return [
            'clock_in' => ['required', 'date_format:H:i'],
            'clock_out' => ['required', 'date_format:H:i', 'after:clock_in'],
            'rests' => ['nullable', 'array'],
            'rests.*.start' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) use ($clockIn, $clockOut) {
                    if ($value && $clockIn && $value < $clockIn) {
                        $fail('休憩時間が不適切な値です');
                    }
                    if ($value && $clockOut && $value > $clockOut) {
                        $fail('休憩時間が不適切な値です');
                    }
                },
            ],
            'rests.*.end' => [
                'nullable',
                'date_format:H:i',
                'after:rests.*.start',
                function ($attribute, $value, $fail) use ($clockOut) {
                    if ($value && $clockOut && $value > $clockOut) {
                        $fail('休憩時間もしくは退勤時間が不適切な値です');
                    }
                },
            ],
            'note' => ['required', 'string', 'max:25'],
        ];
    }

    public function messages()
    {
        return [
            'clock_in.required' => '出勤時間を入力してください',
            'clock_in.date_format' => '出勤時間はHH:MM形式で入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_out.date_format' => '退勤時間はHH:MM形式で入力してください',
            'clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'rests.*.start.date_format' => '休憩開始時間はHH:MM形式で入力してください',
            'rests.*.end.date_format' => '休憩終了時間はHH:MM形式で入力してください',
            'rests.*.end.after' => '休憩時間が勤務時間外です',
            'note.required' => '備考を記入してください',
            'note.max' => '備考は25文字以内で入力してください',
        ];
    }
}
