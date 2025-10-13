<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Invitation;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $token = $request->query('token');
        $invitation = null;

        if ($token) {
            $invitation = Invitation::where('token', $token)
                ->where('expires_at', '>', now())
                ->where('used', false) // Используем used вместо used_at
                ->first();

            if (!$invitation) {
                abort(403, 'Приглашение недействительно или уже использовано.');
            }
        } else {
            // Если нет токена, разрешаем регистрацию только если нет администраторов
            $adminExists = User::where('role', 'admin')->exists();
            if ($adminExists) {
                abort(403, 'Регистрация возможна только по приглашению.');
            }
        }

        return view('auth.register', compact('invitation', 'token'));
    }


    public function register(Request $request)
{
    // Проверка приглашения
    $invitation = Invitation::where('token', $request->token)->first();
    
    if (!$invitation) {
        return redirect()->route('login')
            ->with('error', 'Недействительное приглашение.');
    }
    
    if ($invitation->isExpired()) {
        return redirect()->route('login')
            ->with('error', 'Срок действия приглашения истек.');
    }
    
    if (!$invitation->multi_use && $invitation->used) {
        return redirect()->route('login')
            ->with('error', 'Это приглашение уже было использовано.');
    }
    
    $request->validate([
        'name' => ['required', 'string', 'max:255'],
        'email' => ['required', 'string', 'email', 'max:255', 'unique:'.User::class],
        'password' => ['required', 'confirmed', Rules\Password::defaults()],
    ]);

    $user = User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'role' => $invitation->role,
        'group' => $invitation->role === 'student' ? $invitation->group : null,
        'curator_groups' => $invitation->role === 'curator' ? $invitation->curator_groups : null,
    ]);

    // Обновление статуса приглашения только для одноразовых приглашений
    if (!$invitation->multi_use) {
        $invitation->update(['used' => true]);
    }

    event(new Registered($user));

    Auth::login($user);

    return redirect(RouteServiceProvider::HOME);
}

    /**
     * Handle an incoming registration request.
     */
    public function store(Request $request): RedirectResponse
    {
        $token = $request->input('token');
        $invitation = null;
        $role = 'student'; // По умолчанию
        $group = null;
        $curatorGroups = null;

        if ($token) {
            $invitation = Invitation::where('token', $token)
                ->where('expires_at', '>', now())
                ->where('used', false)
                ->first();

            if (!$invitation) {
                abort(403, 'Приглашение недействительно или уже использовано.');
            }

            $role = $invitation->role;

            // Обработка групп в зависимости от роли
            if ($role === 'student') {
                $group = $invitation->group;
            } elseif ($role === 'curator' && $invitation->group) {
                // Декодируем JSON и проверяем, является ли результат массивом
                $decodedGroups = json_decode($invitation->group, true);
                $curatorGroups = is_array($decodedGroups) ? array_filter($decodedGroups, fn($group) => !empty($group)) : null;
            }
        } else {
            // Если нет токена, разрешаем регистрацию только если нет администраторов
            $adminExists = User::where('role', 'admin')->exists();
            if ($adminExists) {
                abort(403, 'Регистрация возможна только по приглашению.');
            }

            // Первый зарегистрированный пользователь становится администратором
            $role = 'admin';
        }

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $role,
            'group' => $group,
            'curator_groups' => $curatorGroups ? json_encode(array_values($curatorGroups), JSON_UNESCAPED_UNICODE) : null,
        ]);

        // Если использовалось приглашение, отмечаем его как использованное только если оно одноразовое
        if ($invitation && !$invitation->multi_use) {
            $invitation->update([
                'used' => true,
            ]);
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(RouteServiceProvider::HOME);
    }
}