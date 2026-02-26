<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'login_code' => ['required', 'string'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! $this->attemptWithAvailableIdentifierColumns()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'login_code' => trans('auth.failed'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    private function attemptWithAvailableIdentifierColumns(): bool
    {
        $identifier = (string) $this->string('login_code');
        $password = (string) $this->string('password');
        $remember = $this->boolean('remember');

        $identifierColumns = $this->resolveIdentifierColumns();
        foreach ($identifierColumns as $column) {
            if (Auth::attempt([$column => $identifier, 'password' => $password], $remember)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function resolveIdentifierColumns(): array
    {
        static $cached;

        if (is_array($cached)) {
            return $cached;
        }

        $cached = [];

        if (Schema::hasColumn('users', 'login_code')) {
            $cached[] = 'login_code';
        }

        if (Schema::hasColumn('users', 'email')) {
            $cached[] = 'email';
        }

        if (empty($cached)) {
            $cached[] = 'email';
        }

        return $cached;
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'login_code' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('login_code')).'|'.$this->ip());
    }
}
