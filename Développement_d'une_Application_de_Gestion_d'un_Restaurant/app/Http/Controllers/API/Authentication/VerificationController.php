<?php

namespace App\Http\Controllers\API\Authentication;

use App\Http\Controllers\API\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class VerificationController extends Controller
{
    use ApiResponse;


    public function verificationEmail(Request $request)
    {
        $user = User::findOrFail($request->route('id'));

        if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
            return response()->json(['message' => 'Invalid verification link.'], 400);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email already verified.']);
        }

        if ($user->markEmailAsVerified())
            event(new Verified($user));
        return $this->apiResponse( true,'200','email successfully verified');


    }
    public function resendVerificationEmail(){
        $user=auth('users')->user()->sendEmailVerificationNotification();
        return $this->apiResponse($user,'200','verification email resent');

    }
    //
}
