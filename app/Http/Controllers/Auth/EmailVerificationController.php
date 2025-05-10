<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\VerifyEmailPinRequest;
use App\Models\User;
use App\Services\EmailVerificationService;
use Illuminate\Http\JsonResponse;

final class EmailVerificationController extends Controller
{
    public function verify(VerifyEmailPinRequest $request, EmailVerificationService $service): JsonResponse
    {
        $user = User::where('email', $request->input('email'))->firstOrFail();

        if ($service->verifyPin($user, $request->input('pin'))) {
            return response()->json(['message' => 'Email verified successfully.']);
        }

        return response()->json(['message' => 'Invalid or expired PIN.'], 422);
    }
} 