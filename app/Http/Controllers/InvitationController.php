<?php
namespace App\Http\Controllers;

use App\Models\Invitation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    // Список приглашений
    public function index(Request $request)
    {
        $user = Auth::user();
        $search = $request->input('search');
        
        // Базовый запрос
        $query = Invitation::with('creator');
        
        // Поиск по запросу
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('token', 'like', "%{$search}%")
                  ->orWhere('role', 'like', "%{$search}%")
                  ->orWhere('group', 'like', "%{$search}%")
                  ->orWhereHas('creator', function($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        // Администраторы видят все приглашения
        if ($user->isAdmin()) {
            $invitations = $query->latest()->paginate(10)->withQueryString();
        }
        // Кураторы видят только свои приглашения
        elseif ($user->isCurator()) {
            $invitations = $query->where('created_by', $user->id)
                ->latest()
                ->paginate(10)
                ->withQueryString();
        }
        // Студенты не имеют доступа к этой странице
        else {
            abort(403, 'Unauthorized action.');
        }
        
        return view('invitations.index', compact('invitations', 'search'));
    }
    
    // Форма создания приглашения
    public function create()
    {
        $user = Auth::user();
        
        // Только администраторы могут создавать приглашения
        if (!$user->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Получаем список групп
        $groups = User::distinct()->pluck('group')->filter()->values()->toArray();
        
        // Определяем, какие роли может создавать текущий пользователь
        $availableRoles = ['student', 'curator', 'admin'];
        
        return view('invitations.create', compact('groups', 'availableRoles'));
    }
    
    // Сохранение нового приглашения
    public function store(Request $request)
    {
        // Проверка прав доступа
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        // Валидация
        $validated = $request->validate([
            'role' => 'required|in:student,curator,admin',
            'group' => 'nullable|string',
            'new_group' => 'nullable|string',
            'expires_at' => 'nullable|date|after:now',
            'multi_use' => 'nullable|boolean',
        ]);
        
        // Генерация токена
        $token = Invitation::generateToken();
        
        // Определение группы (существующая или новая)
        $group = null;
        if ($validated['role'] === 'student') {
            if (!empty($validated['new_group'])) {
                $group = $validated['new_group'];
            } elseif (!empty($validated['group'])) {
                $group = $validated['group'];
            }
        }
        
        // Создание приглашения
        $invitation = Invitation::create([
            'token' => $token,
            'role' => $validated['role'],
            'group' => $group,
            'created_by' => Auth::id(),
            'used' => false,
            'multi_use' => $request->has('multi_use'),
            'expires_at' => $validated['expires_at'] ?? now()->addDays(7),
        ]);
        
        return redirect()->route('invitations.index')
            ->with('success', 'Приглашение успешно создано.');
    }
    
    // Отображение информации о приглашении
    public function show(Invitation $invitation)
    {
        $user = Auth::user();
        
        // Проверка прав доступа
        if (!$user->isAdmin() && $invitation->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $registrationUrl = route('register', ['token' => $invitation->token]);
        
        return view('invitations.show', compact('invitation', 'registrationUrl'));
    }
    
    // Удаление приглашения
    public function destroy(Invitation $invitation)
    {
        $user = Auth::user();
        
        // Проверка прав доступа
        if (!$user->isAdmin() && $invitation->created_by !== $user->id) {
            abort(403, 'Unauthorized action.');
        }
        
        $invitation->delete();
        
        return redirect()->route('invitations.index')
            ->with('success', 'Приглашение успешно удалено.');
    }
}
