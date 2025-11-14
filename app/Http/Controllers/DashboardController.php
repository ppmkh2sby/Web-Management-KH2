<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        $cards = collect(config('features.cards', []))
            ->filter(function ($c) use ($user) {
                $roles = $c['roles'] ?? [];
                $role  = $user->role instanceof \BackedEnum ? $user->role->value : (string) $user->role;
                return in_array($role, $roles, true);
            })
            ->map(function ($c) {
                $route = $c['route'] ?? null;
                $url   = null;
                if (is_string($route) && $route !== '' && function_exists('route')) {
                    try { $url = route($route); } catch (\Throwable $e) { $url = null; }
                }
                $c['url'] = $url;
                return $c;
            })
            ->values();

        return view('auth.dashboard', compact('user', 'cards'));
    }
}
