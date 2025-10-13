<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Laravel\Fortify\Http\Requests\LoginRequest as FortifyLoginRequest;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserLoginRequest extends FortifyLoginRequest
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
            'email' => 'required',
            'password' => 'required',

        ];
    }
    public function messages()
    {
        return [
            'email.required' => 'メールアドレスを入力してください',
            'password.required' => 'パスワードを入力してください',

        ];
    }

    //ログイン情報がない場合のバリデーションとメッセージの表示
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $email = $this->input('email');
            $password = $this->input('password');

            $user = User::where('email', $email)->first();

            if (! $user) {
                // ユーザーが存在しない場合
                $validator->errors()->add('email', 'ログイン情報が登録されていません');
            } elseif (! Hash::check($password, $user->password)) {
                // パスワードが間違っている場合
                $validator->errors()->add('password', 'パスワードが正しくありません');
            }
        });
    }
}
