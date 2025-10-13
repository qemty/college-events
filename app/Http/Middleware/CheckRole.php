<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $roles  Comma-separated list of roles
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $roles)
    {
        if (!Auth::check()) {
            return redirect('login');
        }

        $user = Auth::user();
        
        // Логируем информацию для отладки
        \Illuminate\Support\Facades\Log::info('CheckRole middleware', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'user_role' => $user->role,
            'requested_roles' => $roles,
            'path' => $request->path(),
            'is_admin' => $user->isAdmin(),
            'is_curator' => $user->isCurator(),
            'is_student' => $user->isStudent(),
        ]);
        
        // Разделяем строку ролей на массив
        $roleArray = explode(',', $roles);
        
        // Проверяем каждую роль
        foreach ($roleArray as $role) {
            $role = trim($role);
            
            \Illuminate\Support\Facades\Log::info('Checking role', [
                'role' => $role,
                'user_role' => $user->role,
                'is_admin' => $user->isAdmin(),
                'is_curator' => $user->isCurator(),
            ]);
            
            // Администратор имеет доступ ко всему
            if ($user->isAdmin()) {
                \Illuminate\Support\Facades\Log::info('Access granted: user is admin');
                return $next($request);
            }
            
            // Проверка для роли куратора
            if ($role === 'curator' && $user->isCurator()) {
                \Illuminate\Support\Facades\Log::info('Access granted: user is curator and role required is curator');
                return $next($request);
            }
            
            // Проверка для роли студента
            if ($role === 'student' && $user->isStudent()) {
                \Illuminate\Support\Facades\Log::info('Access granted: user is student and role required is student');
                return $next($request);
            }
        }
        
        \Illuminate\Support\Facades\Log::warning('Access denied', [
            'user_role' => $user->role,
            'requested_roles' => $roles,
        ]);
        
        return abort(403, 'Unauthorized action.');
    }
}
