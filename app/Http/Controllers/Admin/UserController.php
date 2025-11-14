<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Enum\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $role = $request->query('role');
        $q  = trim((string) $request->query('q'));

        $roles = array_map(fn($r) => $r->value, Role::cases());

        $counts = User::select('role', DB::raw('count(*) as total'))
            ->groupBy('role')
            ->pluck('total', 'role')
            ->all();

        $users = User::query()
            ->with('santri')
            ->when($role, fn($qq) => $qq->where('role', $role))
            ->when($q, function ($qq) use ($q) {
                $qq->where(function ($w) use ($q) {
                    $w->where('name', 'like', "%{$q}%") 
                        ->orWhere('email', 'like', "%{$q}%")
                        ->orWhere('phone', 'like', "%{$q}%");
                });
            })
            ->orderBy('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'roles', 'counts', 'role', 'q'));
    }
}
