<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceActionRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'action' => ['required', 'string', 'in:clock_in,clock_out,break_start,break_end'],
        ];
    }

    public function messages()
    {
        return [
            'action.required' => '操作を選択してください',
            'action.in' => '不正な操作です',
        ];
    }
}
