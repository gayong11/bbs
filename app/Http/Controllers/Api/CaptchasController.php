<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class CaptchasController extends Controller
{
    /**
     * 创建图片验证码
     *
     * @param CaptchaRequest $request
     * @param CaptchaBuilder $captchaBuilder
     *
     * @return mixed
     */
    public function store(CaptchaRequest $request)
    {
        $phone = $request->phone;
        // 缓存key
        $key = 'captcha-' . str_random(15);
        // 生成4位数字图片验证码
        $phrase = new PhraseBuilder(4, '0123456789');
        $captchaBuilder = new CaptchaBuilder(null, $phrase);
        $captcha = $captchaBuilder->build();
        // 缓存有效期
        $expiredAt = now()->addMinutes(2);
        \Cache::put($key, ['phone' => $phone, 'code' => $captcha->getPhrase()], $expiredAt);

        $result = [
            'code'                  => $captcha->getPhrase(),
            'expired_at'            => $expiredAt,
            'captcha_key'           => $key,
            'captcha_image_content' => $captcha->inline(),
        ];

        return $this->response()->array($result)->setStatusCode(201);
    }
}
