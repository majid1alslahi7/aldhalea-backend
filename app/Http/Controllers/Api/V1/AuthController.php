<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends BaseController
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'username' => $validated['username'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'phone' => $validated['phone'] ?? null,
            'role' => 'reader',
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->createdResponse([
            'user' => $user,
            'token' => $token,
        ], 'تم التسجيل بنجاح');
    }

    public function login(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if (!$user || !Hash::check($validated['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['بيانات الدخول غير صحيحة'],
            ]);
        }

        if (!$user->is_active) {
            return $this->errorResponse('الحساب معطل', 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->update(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);

        return $this->successResponse([
            'user' => $user,
            'token' => $token,
        ], 'تم تسجيل الدخول');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return $this->successResponse(null, 'تم تسجيل الخروج');
    }

    public function me(Request $request)
    {
        return $this->successResponse($request->user()->load('preferences'));
    }

    public function updateProfile(Request $request)
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'bio' => 'nullable|string|max:500',
            'location' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'social_links' => 'nullable|array',
        ]);

        $request->user()->update($validated);
        return $this->updatedResponse($request->user());
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8|confirmed',
        ]);

        if (!Hash::check($validated['current_password'], $request->user()->password)) {
            return $this->errorResponse('كلمة المرور الحالية غير صحيحة', 400);
        }

        $request->user()->update(['password' => Hash::make($validated['new_password'])]);
        return $this->successResponse(null, 'تم تغيير كلمة المرور');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        Password::sendResetLink($request->only('email'));

        return $this->successResponse(null, 'تم إرسال رابط إعادة التعيين');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function($user, $password) {
            $user->update(['password' => Hash::make($password)]);
        });

        return $status === Password::PASSWORD_RESET
            ? $this->successResponse(null, 'تم إعادة تعيين كلمة المرور')
            : $this->errorResponse('فشل إعادة التعيين');
    }

    public function verifyEmail(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->successResponse(null, 'البريد مفعل مسبقاً');
        }

        $request->user()->sendEmailVerificationNotification();
        return $this->successResponse(null, 'تم إرسال رابط التفعيل');
    }
}
