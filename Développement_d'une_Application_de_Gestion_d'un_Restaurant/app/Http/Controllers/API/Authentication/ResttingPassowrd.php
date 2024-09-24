<?php

namespace App\Http\Controllers\API\Authentication;

use App\Http\Controllers\API\ApiResponse;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ResttingPassowrd extends Controller
{
    use ApiResponse;
    public function sendLinkRestEmail(Request $request)
    {
        $validator=Validator::make($request->all(),[
            'email'=>'required|email|exists:users',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(null,422,$validator->errors());

        }
        $status=Password::sendResetLink($request->only('email'));
        return $status === Password::RESET_LINK_SENT?
            $this->apiResponse(null,200,$status):
            $this->apiResponse(null,400,$status);






    }
    public function resetPassword(Request $request){
        $validator=Validator::make($request->all(),[
            'email'=>'required|email|exists:users',
            'token'=>'required',
            'password'=>'required|string|confirmed|min:8',
        ]);
        if ($validator->fails()) {
            return $this->apiResponse(null,422,$validator->errors());
        }
        $status=Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->setRememberToken(Str::random(40));
                $user->save();
            }
        );
        return $status === Password::PASSWORD_RESET?
            $this->apiResponse(null,200,$status):
            $this->apiResponse(null,400,$status);
    }
    //
}
