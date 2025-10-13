<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    // Конструктор перемещен в базовый класс Controller или middleware указывается в маршрутах
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->middleware('check.role:admin')->except(['profile', 'updateProfile']);
    // }

    // Список пользователей с фильтрацией и сортировкой
    public function index(Request $request)
    {
        $search = $request->input('search');
        $role = $request->input('role');
        $group = $request->input('group');
        $sort = $request->input('sort', 'name_asc');

        $query = User::query();

        // Поиск
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%');
            });
        }

        // Фильтр по роли
        if ($role) {
            $query->where('role', $role);
        }

        // Фильтр по группе
        if ($group) {
            $query->where('group', $group);
        }

        // Сортировка
        switch ($sort) {
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            case 'email_asc':
                $query->orderBy('email', 'asc');
                break;
            case 'email_desc':
                $query->orderBy('email', 'desc');
                break;
            case 'role_asc':
                $query->orderBy('role', 'asc');
                break;
            case 'role_desc':
                $query->orderBy('role', 'desc');
                break;
            case 'group_asc':
                $query->orderBy('group', 'asc');
                break;
            case 'group_desc':
                $query->orderBy('group', 'desc');
                break;
            default:
                $query->orderBy('name', 'asc');
        }

        $users = $query->paginate(15);
        $groups = User::distinct()->pluck('group')->filter()->values();

        return view('users.index', compact('users', 'search', 'role', 'group', 'sort', 'groups'));
    }

    // Форма редактирования пользователя
    public function edit(User $user)
    {
        $groups = User::distinct()->pluck('group')->filter()->values();
        return view('users.edit', compact('user', 'groups'));
    }

    // Обновление данных пользователя
    public function update(Request $request, User $user)
{
    // Проверка прав доступа
    if (!Auth::user()->isAdmin()) {
        abort(403, 'Unauthorized action.');
    }
    
    // Валидация
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        'role' => 'required|in:admin,curator,student',
        'group' => 'nullable|string|max:255',
        'curator_groups' => 'nullable|array',
        'curator_groups.*' => 'nullable|string|max:255',
        'email_notifications' => 'boolean',
    ]);
    
    // Обновление пользователя
    $user->update([
        'name' => $validated['name'],
        'email' => $validated['email'],
        'role' => $validated['role'],
        'group' => $validated['role'] === 'student' ? $validated['group'] : null,
        'curator_groups' => $validated['role'] === 'curator' ? $validated['curator_groups'] : null,
        'email_notifications' => $request->has('email_notifications'),
    ]);
    
    return redirect()->route('users.index')
        ->with('success', 'Пользователь успешно обновлен.');
}

    // Удаление пользователя
    public function destroy(User $user)
    {
        // Проверка, чтобы администратор не мог удалить сам себя
        if ($user->id === Auth::id()) {
            return redirect()->route('users.index')->with('error', 'Вы не можете удалить свою учетную запись.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Пользователь успешно удален.');
    }

    // Профиль пользователя
    public function profile()
    {
        $user = Auth::user();
        $groups = User::distinct()->pluck('group')->filter()->values();
        return view('users.profile', compact('user', 'groups'));
    }

    // Обновление профиля пользователя
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'current_password' => 'nullable|required_with:password|string',
            'password' => 'nullable|string|min:8|confirmed',
            'theme' => 'required|string|in:light,dark',
            'email_notifications' => 'nullable|boolean',
            'curator_groups' => 'nullable|array',
            'curator_groups.*' => 'nullable|string',
        ]);

        // Проверка текущего пароля
        if (isset($validated['current_password'])) {
            if (!Hash::check($validated['current_password'], $user->password)) {
                return back()->withErrors(['current_password' => 'Текущий пароль указан неверно.']);
            }
        }

        // Обновление пароля, если он был предоставлен
        if (isset($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Обновление имени, email и темы
        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->theme = $validated['theme'];
        $user->email_notifications = $request->has('email_notifications');

        // Если пользователь куратор, обновляем его группы
        if ($user->isCurator() && $request->has('curator_groups')) {
            $curatorGroups = array_filter($request->input('curator_groups', []), fn($group) => !empty($group));
            $user->curator_groups = !empty($curatorGroups) ? json_encode(array_values($curatorGroups), JSON_UNESCAPED_UNICODE) : null;
        }

        $user->save();

        return redirect()->route('profile')->with('success', 'Профиль успешно обновлен.');
    }
}
