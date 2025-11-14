<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Kurangi waktu tunggu SMTP agar tidak blocking lama
        config(['mail.mailers.smtp.timeout' => (int) (env('MAIL_TIMEOUT', 5))]);

        try {
            $request->user()->sendEmailVerificationNotification();
        } catch (\Throwable $e) {
            // Fallback: coba mailer failover, lalu log, tanpa memblokir pengguna
            try {
                config(['mail.default' => 'failover']);
                $request->user()->sendEmailVerificationNotification();
            } catch (\Throwable $e2) {
                try {
                    config(['mail.default' => 'log']);
                    $request->user()->sendEmailVerificationNotification();
                } catch (\Throwable $e3) {
                    // Sebagai jalan terakhir, hanya log error tanpa menghentikan alur
                    logger()->warning('Gagal mengirim verifikasi email', [
                        'user_id' => $request->user()->id,
                        'error' => $e3->getMessage(),
                    ]);
                }
            }
        }

        return back()->with('status', 'verification-link-sent');
    }
}
