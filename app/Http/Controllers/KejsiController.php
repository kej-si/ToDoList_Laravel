<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Http\Requests\RegistrationRequest;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;
use PragmaRX\Google2FA\Google2FA;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Notifications\PasswordResetNotification;
use App\Models\PasswordReset;
use Illuminate\Http\JsonResponse;


class KejsiController extends Controller
{
    //registration method
    public function register(RegistrationRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        
        if ($user) {
            return response()->json([
                'status' => 'success',
                'message' => 'User registered successfully',
                'user' => $user
            ], 201);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'An error occurded while creating user'
            ], 500);
        }
    }

     //return the token
     public function responseToken($token, $user)
     {
         return response()->json([
             'status' => 'success',
             'user' => $user,
             'access_token' => $token,
             'type' => 'bearer'
         ]);
     }

    //login method
    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->attempt($credentials)) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Invalid credentials'
            ], 401);
        }
        $user = auth()->user();
        
        if ($user->google2fa_secret) {
            if (!$request->has('key')) {
                return response()->json([
                    'status' => 'failed',
                    'message' => '2FA key is required',
                    'requires_2fa' => true
                ], 401);
            }
            
            $google2fa = new Google2FA();
            $google2fa->setWindow(0);
            
            $isValid = $google2fa->verifyKey(
                $user->google2fa_secret,
                $request->input('key'),
                false
            );
            
            if (!$isValid) {
                return response()->json([
                    'status' => 'failed',
                    'message' => 'Invalid or expired 2FA key',
                    'requires_2fa' => true
                ], 401);
            }
        }

        return $this->responseToken($token, $user);
    }

public function enable2FA(Request $request)
{
    $user = auth()->user();
    $google2fa = new Google2FA();


    $key = $google2fa->generateSecretKey();


    $qrCodeUrl = $google2fa->getQRCodeUrl(
        config('app.name'),
        $user->email,
        $key
    );

    //save secret key
    $user->google2fa_secret = $key;
    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => '2FA setup initiated',
        'key' => $key,
        'qr_code_url' => $qrCodeUrl
    ]);
}

public function verify2FASetup(Request $request)
{
    $request->validate([
        'key' => 'required|string'
    ]);

    $user = auth()->user();
    $google2fa = new Google2FA();

    $isValid = $google2fa->verifyKey(
        $user->google2fa_secret,
        $request->input('key'),
        false
    );

    if (!$isValid) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Invalid verification code'
        ], 400);
    }

    return response()->json([
        'status' => 'success',
        'message' => '2FA has been successfully enabled'
    ]);
}

// password forgot and reset
public function forgot(ForgotPasswordRequest $request)
{
    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json([
            'status' => 'failed',
            'message' => 'Email address is incorrect or doesn\'t exist'
        ], 404);
    }

    $resetPasswordToken = str_pad(random_int(1, 9999), 4, '0', STR_PAD_LEFT);

    $passwordReset = PasswordReset::updateOrCreate(
        ['email' => $user->email],
        [
            'token' => $resetPasswordToken,
            'created_at' => now()
        ]
    );

    // Send notification to user
    $user->notify(new PasswordResetNotification($resetPasswordToken));

    return response()->json([
        'status' => 'success',
        'message' => 'A code has been sent to your email address'
    ]);
}

public function resetPassword(Request $request)
{
    $request->validate([
        'email' => 'required|email|exists:users,email',
        'password' => 'required|string|min:8|confirmed'
    ]);

    $user = User::where('email', $request->email)->first();
    
    if (!$user) {
        return response()->json([
            'status' => 'failed',
            'message' => 'User not found'
        ], 404);
    }

    $user->password = Hash::make($request->password);
    $user->save();

    return response()->json([
        'status' => 'success',
        'message' => 'Password has been reset successfully'
    ]);
}
}