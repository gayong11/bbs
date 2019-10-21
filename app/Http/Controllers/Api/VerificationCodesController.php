<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\VerificationCodeRequest;
use Overtrue\EasySms\EasySms;

/**
 * Class VerificationCodesController
 * @package App\Http\Controllers\Api
 */
class VerificationCodesController extends Controller
{
    /**
     * 发送短信验证码
     *
     * @param VerificationCodeRequest $request
     * @param EasySms                 $easySms
     */
    public function store(VerificationCodeRequest $request, EasySms $easySms)
    {
        $captchaData = \Cache::get($request->captcha_key);
        if (!$captchaData) {
            return $this->response()->error('图片验证码已失效', 422);
        }
        if (!hash_equals((string)$captchaData['code'], (string)$request->captcha_code)) {
            // 验证错误清楚缓存
            \Cache::forget($request->catpcah_key);

            return $this->response()->errorUnauthorized('验证码错误');
        }
        $phone = $captchaData['phone'];

        // 验证码有效期
        $time = 10;
        // 不是正式环境时
        if (!app()->environment('production')) {
            $code = 1234;
        } else {
            // 随机生成4位code,不足时向左补零
            $code = str_pad(random_int(1, 9999), 4, 0, STR_PAD_LEFT);
            try {
                $easySms->send($phone, [
                    'content'  => "您的验证码为：{ $code }",
                    'template' => 1,
                    'data'     => [
                        $code,
                        $time,
                    ],
                ]);
            } catch (\Overtrue\EasySms\Exceptions\NoGatewayAvailableException $exception) {
                $message = $exception->getException('yuntongxun')->getMessage();

                return $this->response()->errorInternal($message ?: '短信发送异常');
            }
        }

        // 缓存key
        $key = 'verificationCode_' . str_random(15);
        // 缓存有效期
        $expiredAt = now()->addMinutes($time);
        // 缓存验证码
        \Cache::put($key, ['phone' => $phone, 'code' => $code], $expiredAt);
        // 清除图片验证码key
        \Cache::forget($request->captcha_key);

        return $this->response()->array([
            'key'        => $key,
            'expired_at' => $expiredAt,
        ])->setStatusCode(201);
    }
}
