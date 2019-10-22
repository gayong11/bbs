<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\CaptchaRequest;
use Gregwar\Captcha\CaptchaBuilder;
use Gregwar\Captcha\PhraseBuilder;

class CaptchasController extends Controller
{
    /*

      $accessToken = '26_JJ0ZtDRjlBSe7vUXqp8tHNectYrvE9eOwIcCP2KHYv7DAluAF8PgDbsPHhilHcGt3EDPHhst8smte7LgwgIzjg';
      $openId = 'owU8E1YDD0ak9839s0ZMnJ4oaQ08';
      $driver = Socialite::driver('weixin');
      $driver->setOpenId($openId);
      $oauthUser = $driver->userFromToken($accessToken);



    $code = '071TdTp91ZFNJO1Vlco91GL9q91TdTpb';
    $driver = Socialite::driver('weixin');
    $response = $driver->getAccessTokenResponse($code);
    $driver->setOpenId($response['openid']);
    $oauthUser = $driver->userFromToken($response['access_token']);



      https://open.weixin.qq.com/connect/oauth2/authorize?appid=wx1e3992c05d784fbd&redirect_uri=http://bbs.test&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect

      https://api.weixin.qq.com/sns/oauth2/access_token?appid=wx1e3992c05d784fbd&secret=ea1237bc592c6054cb155a9079b1f742&code=001SuLFQ0F7Ns72uipFQ0ip1GQ0SuLFN&grant_type=authorization_code

      https://api.weixin.qq.com/sns/userinfo?access_token=26_HIQQVfp86vDr0u8hBHS0UjGvQvygL3u6rT91DoWfFGzkUlDWS5S3ug_o2lZ7Zu3rJdnWZ6vDIN_ZJ3M15InMVA&openid=owU8E1YDD0ak9839s0ZMnJ4oaQ08&lang=zh_CN

     */

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
