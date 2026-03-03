<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * @var array<int, string>
     */
    private const IDENTIFIER_COLUMNS = ['login_code', 'email'];

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
            try {
                if (Auth::attempt([$column => $identifier, 'password' => $password], $remember)) {
                    return true;
                }
            } catch (QueryException $exception) {
                // Fallback for older DB schema that may not yet have login_code column.
                if (! $this->isMissingIdentifierColumn($exception, $column)) {
                    throw $exception;
                }
            }
        }

        return false;
    }

    /**
     * @return array<int, string>
     */
    private function resolveIdentifierColumns(): array
    {
        return self::IDENTIFIER_COLUMNS;
    }

    private function isMissingIdentifierColumn(QueryException $exception, string $column): bool
    {
        $sqlState = (string) ($exception->errorInfo[0] ?? '');
        $message = strtolower($exception->getMessage());
        $needle = strtolower($column);

        return $sqlState === '42703' || str_contains($message, "column \"{$needle}\" does not exist");
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
