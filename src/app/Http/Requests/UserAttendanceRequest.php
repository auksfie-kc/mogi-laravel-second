<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;
class UserAttendanceRequest extends FormRequest
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
            'end_time'     => 'nullable|date_format:H:i',
            'rest1_start'  => 'nullable|date_format:H:i',
            'rest1_end'    => 'nullable|date_format:H:i',
            'rest2_start'  => 'nullable|date_format:H:i',
            'rest2_end'    => 'nullable|date_format:H:i',
            'reason'       => 'required|string',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $start = $this->input('start_time') ? Carbon::parse($this->input('start_time')) : null;
            $end   = $this->input('end_time') ? Carbon::parse($this->input('end_time')) : null;

            //日を跨ぐ場合は退勤を翌日扱い
            if ($start && $end && $end->lessThan($start)) {
                $end->addDay();
            }

            // 出退勤チェック
            if ($start && $end && $end->lessThan($start)) {
                $validator->errors()->add('end_time', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩チェックをまとめる
            $rests = [
                ['start' => 'rest1_start', 'end' => 'rest1_end'],
                ['start' => 'rest2_start', 'end' => 'rest2_end'],
            ];

            foreach ($rests as $rest) {
                $rStart = $this->input($rest['start']) ? Carbon::createFromFormat('H:i', $this->input($rest['start'])) : null;
                $rEnd   = $this->input($rest['end']) ? Carbon::createFromFormat('H:i', $this->input($rest['end'])) : null;

                // 休憩終了が開始より前なら翌日扱い
                if ($rStart && $rEnd && $rEnd->lessThan($rStart)) {
                    $rEnd->addDay();
                }

                // 開始と終了の順序チェック
                if ($rStart && $rEnd && $rEnd->lessThan($rStart)) {
                    $validator->errors()->add($rest['end'], '休憩時間が不適切な値です');
                }

                // 休憩終了が退勤を超える場合
                if ($rEnd && $end && $rEnd->greaterThan($end)) {
                    $validator->errors()->add($rest['end'], '休憩時間あるいは退勤時間が不適切な値です');
                }
            }
        });
    }
}
