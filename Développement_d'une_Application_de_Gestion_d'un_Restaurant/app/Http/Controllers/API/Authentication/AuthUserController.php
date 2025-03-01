<?php

namespace App\Http\Controllers\API\Authentication;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
class AuthUserController extends Controller
{
    public function __construct() {
        $this->middleware(['jwt.verify:users'], ['except' => ['login', 'register']]);
    }
    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request){
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }


        if (! $token = auth()->guard('users')->attempt($validator->validated())) {
            return response()->json(['error' => 'email or password incorrect '], 401);
        }

        return $this->createNewToken($token);
    }
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'password' => 'required|string|confirmed|min:6',
        ]);

        if($validator->fails()){
            return response()->json($validator->errors()->toJson(), 400);
        }

        $user = User::create(array_merge(
            $validator->validated(),
            ['password' => bcrypt($request->password)]
        ));
        $user->sendEmailVerificationNotification();
        $user->profile()->create();
        $token= auth('users')->login($user);
        $this->createNewToken($token);


        return response()->json([
            'message' => 'User successfully registered',
            'token' => $token,
            'user' => $user
        ], 201);
    }
    public function logout() {
        auth()->guard('users')->logout();

        return response()->json(['message' => 'User successfully signed out','status' => 200]);
    }
    public function refresh() {
        return $this->createNewToken(auth()->guard('users')->refresh());
    }

    /**
     * Get the authenticated User.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function userProfile() {
        return response()->json(auth()->guard('users')->user()->load('profile'));
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function createNewToken($token){
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'user' => auth()->guard('users')->user()
        ]);
    }


    //
}
