<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\EmailVerificationPin;
use App\Models\User;
use App\Models\EmailVerificationPin as EmailVerificationPinModel;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

final class EmailVerificationService
{
    public function sendVerificationPin(User $user): void
    {
        $pin = (string)random_int(100000, 999999);
        
        EmailVerificationPinModel::updateOrCreate(
            ['user_id' => $user->id],
            [
                'pin' => $pin,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );

        Mail::to($user->email)->send(new EmailVerificationPin($user, $pin));
    }

    public function verifyPin(User $user, string $pin): bool
    {
        $record = EmailVerificationPinModel::where('user_id', $user->id)
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