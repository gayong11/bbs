<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\UserRequest;
use App\Models\User;

class UsersController extends Controller
{
    /**
     * 用户注册
     * @param UserRequest $request
     *
     * @return \Dingo\Api\Http\Response|void
     */
    public function store(UserRequest $request)
    {
        $verifyData = \Cache::get($request->verification_key);
        if (! $verifyData) {
            return $this->response()->error('验证码已失效', 422);
        }
        if (! hash_equals((string)$verifyData['code'], (string)$request->verification_code)) {
            return $this->response()->errorUnauthorized('验证码错误');
        }

        $user = User::create([
            'name' => $request->name,
            'phone' => $verifyData['phone'],
            'password' => bcrypt($request->password),
        ]);
        // 清楚验证码缓存
        \Cache::forget($request->verification_key);

        return $this->response()->created();
    }
}
