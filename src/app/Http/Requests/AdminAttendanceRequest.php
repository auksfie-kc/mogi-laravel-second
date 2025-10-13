<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time'   => 'nullable|date_format:H:i',
            'end_time'     => 'nullable|date_format:H:i|after_or_equal:start_time',
            'rest1_start'  => 'nullable|date_format:H:i|after_or_equal:start_time',
            'rest1_end'    => 'nullable|date_format:H:i|after_or_equal:end_time',
            'rest2_start'  => 'nullable|date_format:H:i|after_or_equal:start_time',
            'rest2_end'    => 'nullable|date_format:H:i|after_or_equal:end_time',
            'reason'       => 'required|string',
        ];
    }

    public function messages()
    {
        return [
            'start_time.date_format' => '出勤時間の形式が不正です',
            'end_time.date_format' => '退勤時間の形式が不正です',
            'end_time.after_or_equal:start_time' => '出勤時間または退勤時間が不適切な値です',
            'rest1_start.date_format' => '休憩時間の形式が不正です',
            'rest1_start.after_or_equal:start_time' => '休憩時間が不適切な値です',
            'rest1_end.date_format' => '休憩時間の形式が不正です',
            'rest1_end.after_or_equal:end_time' => '休憩時間もしくは退勤時間が不適切な値です',
            'rest2_start.date_format' => '休憩時間の形式が不正です',
            'rest2_start.after_or_equal:start_time' => '休憩時間が不適切な値です',
            'rest2_end.date_format' => '休憩時間の形式が不正です',
            'rest2_end.after_or_equal:end_time' => '休憩時間もしくは退勤時間が不適切な値です',
            'reason.required' => '備考を記入してください',

        ];
    }
}
