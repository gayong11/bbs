<?php

namespace App\Http\Requests\Api;

class VerificationCodeRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     * @return array
     */
    public function rules()
    {
        return [
            'captcha_code' => 'required|string',
            'captcha_key'  => 'required|string',
        ];
    }

    public function attributes()
    {
        return [
            'captcha_code' => '图片验证码',
            'captcha_key'  => '图片验证码 key',
        ];
    }
}
