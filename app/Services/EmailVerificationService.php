<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Models\EmailVerificationPin;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Carbon\Carbon;

final class EmailVerificationService
{
    public function sendVerificationPin(User $user): void
    {
        $pin = random_int(100000, 999999);
        EmailVerificationPin::updateOrCreate(
            ['user_id' => $user->id],
            [
                'pin' => (string)$pin,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        // Send email (use a Mailable for real projects)
        Mail::raw("Your verification PIN is: {$pin}", function ($message) use ($user) {
            $message->to($user->email)
                ->subject('Your Email Verification PIN');
        });
    }

    public function verifyPin(User $user, string $pin): bool
    {
        $record = EmailVerificationPin::where('user_id', $user->id)
            ->where('pin', $pin)
            ->where('expires_at', '>', now())
            ->first();

        if ($record) {
            $user->email_verified_at = now();
            $user->save();
            $record->delete();
            return true;
        }
        return false;
    }
} 