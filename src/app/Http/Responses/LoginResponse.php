<?php

namespace App\Http\Responses;

use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $loginType = $request->session()->get('ui_role', 'admin');

        if ($loginType === 'admin' && $request->user()->is_admin) {
            return redirect()->route('admin.attendance.list');
        }

        return redirect()->intended(config('fortify.home'));
    }
}
