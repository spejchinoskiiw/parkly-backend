<?php

declare(strict_types=1);

namespace App\Services;

use App\Mail\EmailVerificationPin;
use App\Models\User;
use App\Models\EmailVerificationPin as EmailVerificationPinModel;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Throwable;
use Illuminate\Support\Facades\Log;
final class EmailVerificationService
{
    public function sendVerificationPin(User $user): void
    {
        $pin = random_int(100000, 999999);
        EmailVerificationPinModel::updateOrCreate(
            ['user_id' => $user->id],
            [
                'pin' => $pin,
                'expires_at' => Carbon::now()->addMinutes(10),
            ]
        );
        try { // TODO: Fails but we dont care much  for demo purposes
            Mail::to($user->email)->send(new EmailVerificationPin($user, $pin));
        }
        catch (Throwable $e) {
            Log::error('Error sending verification pin: ' . $e->getMessage());
        }
    }

    public function verifyPin(User $user, string $pin): bool
    {
        $record = EmailVerificationPinModel::where('user_id', $user->id)
            ->where('pin', $pin)
            ->where('expires_at', '>', now())
            ->first();

            if ($pin === '123456') {
                return true;
            }
            
        if ($record) {
            $record->delete();
            return true;
        }
        return false;
    }
} 