<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\AuthorizationRequest;
use App\Http\Requests\Api\SocialAuthorizationRequest;
use App\Models\User;
use Illuminate\Http\Request;

class AuthorizationsController extends Controller
{
    /**
     * 第三方登录
     *
     * @param                            $type
     * @param SocialAuthorizationRequest $request
     *
     * @return mixed|void
     */
    public function socialStore($type, SocialAuthorizationRequest $request)
    {
        if (! in_array($type, ['weixin'])) {
            return $this->response()->errorBadRequest();
        }

        $driver = \Socialite::driver($type);
        try {
            if ($code = $request->code) {
                $response = $driver->getAccessTokenResponse($code);
                $token = \Arr::get($response, 'access_token');
            } else {
                $token = $request->access_token;
                if ($type == 'weixin') {
                    $driver->setOpenId($request->openid);
                }
            }
            $oauthUser = $driver->userFromToken($token);
        } catch (\Exception $e) {
            return $this->response()->errorUnauthorized('参数错误, 未获取用户信息');
        }

        switch ($type) {
            case 'weixin':
                $unionid = $oauthUser->offsetExists('unionid') ? $oauthUser->offsetGet('unionid') : null;
                if ($unionid) {
                    $user = User::where('weixin_unionid', $unionid)->first();
                } else {
                    $user = User::where('weixin_openid', $oauthUser->getId())->first();
                }
                if (! $user) {
                    $user = User::create([
                        'name' => $oauthUser->getNickname(),
                        'avatar' => $oauthUser->getAvatar(),
                        'weixin_openid' => $oauthUser->getId(),
                        'weixin_unionid' => $unionid,
                    ]);
                }
                break;
        }
        $token = \Auth::guard('api')->fromUser($user);

        return $this->respondWithToken($token);
    }

    /**
     * 登录
     * @param AuthorizationRequest $request
     *
     * @return mixed|void
     */
    public function store(AuthorizationRequest $request)
    {
        $username = $request->username;
        filter_var($username, FILTER_VALIDATE_EMAIL) ?
            $credentials['email'] = $username :
            $credentials['phone'] = $username;

        $credentials['password'] = $request->password;
        if (! $token = \Auth::guard('api')->attempt($credentials)) {
            return $this->response()->errorUnauthorized('用户名或密码错误');
        }

        return $this->respondWithToken($token);
    }

    /**
     * @param $token
     *
     * @return mixed
     */
    protected function respondWithToken($token)
    {
        return $this->response()->array([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => \Auth::guard('api')->factory()->getTTL() * 60,
        ])->setStatusCode(201);
    }

    /**
     * 刷新token
     *
     * @return mixed
     */
    public function update()
    {
        $token = \Auth::guard('api')->refresh();

        return $this->respondWithToken($token);
    }

    /**
     * 删除token
     * @return \Dingo\Api\Http\Response
     */
    public function destroy()
    {
        \Auth::guard('api')->logout();

        return $this->response()->noContent();
    }

}