<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Registration;
use App\Models\Event;
use App\Models\User;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\EventGroup;
use Carbon\Carbon;


class EventController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $query = Event::query()->orderBy('date_time', 'desc');

        // Получаем параметры фильтров и сортировки
        $tab = $request->input('tab', 'upcoming'); // По умолчанию вкладка "Предстоящие"
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $types = $request->input('types', []);
        $themes = $request->input('themes', []);
        $groups = $request->input('groups', []);
        $sort = $request->input('sort', 'date_time_desc');
        $search = $request->input('search');

        // Поиск по названию, описанию и месту проведения
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                    ->orWhere('description', 'like', '%' . $search . '%')
                    ->orWhere('location', 'like', '%' . $search . '%');
            });
        }

        // Фильтрация по вкладкам
        if ($tab === 'upcoming') {
            $query->where('date_time', '>', now());
        } elseif ($tab === 'past') {
            $query->where('date_time', '<=', now());
        } elseif ($tab === 'my' && $user->isStudent()) {
            $query->whereHas('registrations', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            });
        }

        // Фильтрация мероприятий в зависимости от роли
        if ($user->isStudent()) {
            // Проверяем, что у студента указана группа
            if (!empty($user->group)) {
                // Студент видит только мероприятия своей группы
                $eventIds = EventGroup::where('group', $user->group)->pluck('event_id');

                // Если есть мероприятия для группы, применяем фильтр
                if ($eventIds->count() > 0) {
                    $query->whereIn('id', $eventIds);
                } else {
                    // Если нет мероприятий для группы, показываем сообщение
                    // и возвращаем пустой результат
                    session()->flash('info', 'Для вашей группы пока нет мероприятий.');
                    $query->whereRaw('1 = 0');
                }
            } else {
                // Если группа не указана, показываем сообщение
                session()->flash('warning', 'У вас не указана группа. Обратитесь к администратору.');
                $query->whereRaw('1 = 0');
            }
        }

        // Фильтрация по датам
        if ($dateFrom) {
            $query->where('date_time', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->where('date_time', '<=', Carbon::parse($dateTo)->endOfDay());
        }

        // Фильтрация по типам
        if (!empty($types) && !in_array('All', $types)) {
            $query->whereIn('type', $types);
        }

        // Фильтрация по темам
        if (!empty($themes) && !in_array('All', $themes)) {
            $query->whereIn('theme', $themes);
        }

        // Фильтрация по группам
        if (!empty($groups) && !in_array('All', $groups)) {
            $eventIds = EventGroup::whereIn('group', $groups)->pluck('event_id');
            $query->whereIn('id', $eventIds);
        }

        // Сортировка
        if ($sort === 'date_time_asc') {
            $query->orderBy('date_time', 'asc');
        } elseif ($sort === 'title_asc') {
            $query->orderBy('title', 'asc');
        } elseif ($sort === 'title_desc') {
            $query->orderBy('title', 'desc');
        } else {
            $query->orderBy('date_time', 'desc'); // По умолчанию
        }

        // Получаем мероприятия с пагинацией
        $events = $query->paginate(10);

        // Получаем уникальные типы, темы и группы для фильтров
        $eventTypes = Event::distinct()->pluck('type')->filter()->values()->toArray();
        $eventThemes = Event::distinct()->pluck('theme')->filter()->values()->toArray();
        $allGroups = User::distinct()->pluck('group')->filter()->values()->toArray();

        // Передаем все необходимые переменные в представление
        return view('events.index', compact(
            'events',
            'tab',
            'dateFrom',
            'dateTo',
            'types',
            'themes',
            'groups',
            'sort',
            'eventTypes',
            'eventThemes',
            'allGroups' // Переименовано в allGroups, чтобы избежать конфликта с переменной $groups
        ));
    }


    public function create()
    {
        // Только администраторы могут создавать мероприятия
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Получаем список всех групп
        $availableGroups = User::distinct()->pluck('group')->filter()->values()->toArray();

        // Получаем список типов мероприятий
        $eventTypes = \App\Models\EventType::all();

        return view('events.create', compact('availableGroups', 'eventTypes'));
    }

    // Сохранение нового мероприятия
    public function store(Request $request)
    {
        // Проверка прав доступа
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Валидация
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_time' => 'required|date|after:now',
            'location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1',
            'type' => 'nullable|string|max:255',
            'theme' => 'nullable|string|max:255',
            'groups' => 'required|array|min:1',
            'groups.*' => 'string',
            'new_group' => 'nullable|string',
        ]);

        // Автоматический перевод типа мероприятия с английского на русский
        if (!empty($validated['type'])) {
            // Приводим к нижнему регистру для сравнения
            $typeKey = strtolower($validated['type']);

            // Проверяем, есть ли такой ключ в стандартных типах
            $standardTypes = array_change_key_case(\App\Models\EventType::$types, CASE_LOWER);

            // Если есть прямое соответствие ключу
            if (array_key_exists($typeKey, $standardTypes)) {
                $validated['type'] = $standardTypes[$typeKey];
            }
            // Если введенное значение совпадает с одним из русских названий
            elseif (in_array($validated['type'], \App\Models\EventType::$types)) {
                // Оставляем как есть, так как это уже русское название
            }
            // Если введенное значение совпадает с одним из английских ключей (без учета регистра)
            elseif (($key = array_search(strtolower($validated['type']), array_map('strtolower', array_keys($standardTypes)))) !== false) {
                $keys = array_keys(\App\Models\EventType::$types);
                $validated['type'] = \App\Models\EventType::$types[$keys[$key]];
            }
        }

        // Создание мероприятия
        $event = Event::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date_time' => $validated['date_time'],
            'location' => $validated['location'],
            'max_participants' => $validated['max_participants'],
            'type' => $validated['type'],
            'theme' => $validated['theme'],
            'attendance_token' => Str::random(32),
        ]);

        // Обработка групп
        $groups = $validated['groups'] ?? [];

        // Добавление новой группы, если указана
        if (!empty($validated['new_group'])) {
            $newGroups = array_map('trim', explode(',', $validated['new_group']));
            $groups = array_merge($groups, $newGroups);
        }

        // Сохранение групп для мероприятия
        foreach ($groups as $group) {
            EventGroup::create([
                'event_id' => $event->id,
                'group' => $group,
            ]);
        }

        return redirect()->route('events.index')
            ->with('success', 'Мероприятие успешно создано.');
    }

    // Отображение информации о мероприятии
    public function show(Event $event)
    {
        $user = Auth::user();

        // Проверка доступа к мероприятию в зависимости от роли
        if ($user->isStudent()) {
            $hasAccess = EventGroup::where('event_id', $event->id)
                ->where('group', $user->group)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'У вас нет доступа к этому мероприятию.');
            }
        }
        // Для куратора не проверяем доступ, он видит все мероприятия

        // Получаем группы мероприятия
        $eventGroups = EventGroup::where('event_id', $event->id)->get();

        // Генерируем QR-коды для каждой группы (только для админа и куратора)
        $qrCodes = [];
        if ($user->isAdmin() || $user->isCurator()) {
            foreach ($eventGroups as $eventGroup) {
                // Создаем уникальный токен для каждой группы
                $groupToken = $event->attendance_token . '_' . $eventGroup->group;

                // Генерируем QR-код с информацией о группе
                $qr = new QrCode(route('events.attendance.verify', [
                    'token' => $groupToken,
                    'group' => $eventGroup->group
                ]));
                $qr->setSize(200);
                $writer = new PngWriter();
                $qrCodes[$eventGroup->group] = base64_encode($writer->write($qr)->getString());
            }

            // Если нет групп, создаем общий QR-код
            if (empty($qrCodes) && $event->attendance_token) {
                $qr = new QrCode(route('events.attendance.verify', $event->attendance_token));
                $qr->setSize(200);
                $writer = new PngWriter();
                $qrCodes['default'] = base64_encode($writer->write($qr)->getString());
            }
        }

        // Проверяем, зарегистрирован ли пользователь на мероприятие
        $isRegistered = $event->registrations->contains('user_id', $user->id);

        // Проверяем, отмечено ли посещение
        $hasAttended = $event->attendances->contains(function ($attendance) use ($user) {
            return $attendance->user_id === $user->id && $attendance->attended;
        });

        // Проверяем, можно ли еще зарегистрироваться
        $canRegister = !$isRegistered &&
            $event->registrations->count() < $event->max_participants &&
            $event->date_time > now();

        return view('events.show', compact('event', 'isRegistered', 'hasAttended', 'canRegister', 'eventGroups', 'qrCodes'));
    }

    // Форма редактирования мероприятия
    public function edit(Event $event)
    {
        // Только администраторы могут редактировать мероприятия
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Получаем список всех групп
        $availableGroups = User::distinct()->pluck('group')->filter()->values()->toArray();

        // Получаем группы мероприятия
        $eventGroups = EventGroup::where('event_id', $event->id)->pluck('group')->toArray();

        // Получаем список типов мероприятий
        $eventTypes = \App\Models\EventType::all();

        return view('events.edit', compact('event', 'availableGroups', 'eventGroups', 'eventTypes'));
    }

    // Обновление мероприятия
    public function update(Request $request, Event $event)
    {
        // Только администраторы могут обновлять мероприятия
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Валидация
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'date_time' => 'required|date',
            'location' => 'required|string|max:255',
            'max_participants' => 'required|integer|min:1',
            'type' => 'nullable|string|max:255',
            'theme' => 'nullable|string|max:255',
            'groups' => 'required|array|min:1',
            'groups.*' => 'string',
            'new_group' => 'nullable|string',
        ]);

        // Автоматический перевод типа мероприятия с английского на русский
        if (!empty($validated['type'])) {
            // Приводим к нижнему регистру для сравнения
            $typeKey = strtolower($validated['type']);

            // Проверяем, есть ли такой ключ в стандартных типах
            $standardTypes = array_change_key_case(\App\Models\EventType::$types, CASE_LOWER);

            // Если есть прямое соответствие ключу
            if (array_key_exists($typeKey, $standardTypes)) {
                $validated['type'] = $standardTypes[$typeKey];
            }
            // Если введенное значение совпадает с одним из русских названий
            elseif (in_array($validated['type'], \App\Models\EventType::$types)) {
                // Оставляем как есть, так как это уже русское название
            }
            // Если введенное значение совпадает с одним из английских ключей (без учета регистра)
            elseif (($key = array_search(strtolower($validated['type']), array_map('strtolower', array_keys($standardTypes)))) !== false) {
                $keys = array_keys(\App\Models\EventType::$types);
                $validated['type'] = \App\Models\EventType::$types[$keys[$key]];
            }
        }

        // Обновление мероприятия
        $event->update([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'date_time' => $validated['date_time'],
            'location' => $validated['location'],
            'max_participants' => $validated['max_participants'],
            'type' => $validated['type'],
            'theme' => $validated['theme'],
        ]);

        // Обработка групп
        $groups = $validated['groups'];

        // Добавление новой группы, если указана
        if (!empty($validated['new_group'])) {
            $newGroups = array_map('trim', explode(',', $validated['new_group']));
            $groups = array_merge($groups, $newGroups);
        }

        // Удаляем старые связи с группами
        EventGroup::where('event_id', $event->id)->delete();

        // Сохраняем новые связи с группами
        foreach ($groups as $group) {
            EventGroup::create([
                'event_id' => $event->id,
                'group' => $group,
            ]);
        }

        return redirect()->route('events.show', $event)
            ->with('success', 'Мероприятие успешно обновлено.');
    }

    // Удаление мероприятия
    public function destroy(Event $event)
    {
        // Только администраторы могут удалять мероприятия
        if (Auth::user()->role !== 'admin') {
            abort(403, 'Unauthorized action.');
        }

        // Удаляем связи с группами
        EventGroup::where('event_id', $event->id)->delete();

        // Удаляем мероприятие
        $event->delete();

        return redirect()->route('events.index')
            ->with('success', 'Мероприятие успешно удалено.');
    }

    public function report(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');
        $theme = $request->input('theme');
        $group = $request->input('group');
        $role = $request->input('role');

        $query = Registration::query()->where('is_attendance_confirmed', true);

        if ($dateFrom) {
            $query->whereHas('event', fn($q) => $q->where('date_time', '>=', $dateFrom));
        }
        if ($dateTo) {
            $query->whereHas('event', fn($q) => $q->where('date_time', '<=', $dateTo));
        }
        if ($type) {
            $query->whereHas('event', fn($q) => $q->where('type', $type));
        }
        if ($theme) {
            $query->whereHas('event', fn($q) => $q->where('theme', $theme));
        }
        if ($group) {
            $query->whereHas('user', fn($q) => $q->where('group', $group));
        }
        if ($role) {
            $query->whereHas('user', fn($q) => $q->where('role', $role));
        }

        $registrations = $query->with(['event', 'user'])->get();

        // Базовая аналитика
        $byType = $registrations
            ->groupBy(fn($reg) => $reg->event->type ?? 'Без типа')
            ->map->count()
            ->toArray();

        $byTheme = $registrations
            ->groupBy(fn($reg) => $reg->event->theme ?? 'Без тематики')
            ->map->count()
            ->toArray();

        $byGroup = $registrations
            ->groupBy(fn($reg) => $reg->user->group ?? 'Без группы')
            ->map->count()
            ->toArray();

        $byMonth = $registrations
            ->groupBy(fn($reg) => $reg->event->date_time->format('Y-m'))
            ->map->count()
            ->toArray();

        // Расширенная аналитика
        $byRole = $registrations
            ->groupBy(fn($reg) => $reg->user->role ?? 'Не указана')
            ->map->count()
            ->toArray();

        // Динамика регистраций по дням недели
        $byDayOfWeek = $registrations
            ->groupBy(fn($reg) => $reg->event->date_time->locale('ru')->dayName)
            ->map->count()
            ->toArray();

        // Соотношение максимального количества участников к фактическому
        $attendanceRatio = $registrations
            ->groupBy(fn($reg) => $reg->event->id)
            ->map(function ($regs) {
                $event = $regs->first()->event;
                return [
                    'title' => $event->title,
                    'max' => $event->max_participants,
                    'actual' => $regs->count(),
                    'ratio' => $event->max_participants > 0 ? round(($regs->count() / $event->max_participants) * 100, 1) : 0
                ];
            })
            ->sortByDesc('ratio')
            ->take(10)
            ->toArray();

        // Топ-5 самых популярных мероприятий
        $topEvents = $registrations
            ->groupBy(fn($reg) => $reg->event->id)
            ->map(function ($regs) {
                $event = $regs->first()->event;
                return [
                    'title' => $event->title,
                    'count' => $regs->count(),
                    'date' => $event->date_time->format('d.m.Y')
                ];
            })
            ->sortByDesc('count')
            ->take(5)
            ->toArray();

        $eventTypes = Event::distinct()->pluck('type')->filter()->toArray();
        $eventThemes = Event::distinct()->pluck('theme')->filter()->toArray();
        $userGroups = User::distinct()->pluck('group')->filter()->toArray();
        $userRoles = User::distinct()->pluck('role')->filter()->toArray();

        return view('events.report', compact(
            'dateFrom',
            'dateTo',
            'type',
            'theme',
            'group',
            'role',
            'byType',
            'byTheme',
            'byGroup',
            'byMonth',
            'byRole',
            'byDayOfWeek',
            'attendanceRatio',
            'topEvents',
            'eventTypes',
            'eventThemes',
            'userGroups',
            'userRoles'
        ));
    }

    public function reportPdf(Request $request)
    {
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $type = $request->input('type');
        $theme = $request->input('theme');
        $group = $request->input('group');

        $query = Registration::query()->where('is_attendance_confirmed', true);

        if ($dateFrom) {
            $query->whereHas('event', fn($q) => $q->where('date_time', '>=', $dateFrom));
        }
        if ($dateTo) {
            $query->whereHas('event', fn($q) => $q->where('date_time', '<=', $dateTo));
        }
        if ($type) {
            $query->whereHas('event', fn($q) => $q->where('type', $type));
        }
        if ($theme) {
            $query->whereHas('event', fn($q) => $q->where('theme', $theme));
        }
        if ($group) {
            $query->whereHas('user', fn($q) => $q->where('group', $group));
        }

        $registrations = $query->with(['event', 'user'])->get();

        $byType = $registrations
            ->groupBy(fn($reg) => $reg->event->type ?? 'Без типа')
            ->map->count()
            ->toArray();

        $byTheme = $registrations
            ->groupBy(fn($reg) => $reg->event->theme ?? 'Без тематики')
            ->map->count()
            ->toArray();

        $byGroup = $registrations
            ->groupBy(fn($reg) => $reg->user->group ?? 'Без группы')
            ->map->count()
            ->toArray();

        $byMonth = $registrations
            ->groupBy(fn($reg) => $reg->event->date_time->format('Y-m'))
            ->map->count()
            ->toArray();

        $eventTypes = Event::distinct()->pluck('type')->filter()->toArray();
        $eventThemes = Event::distinct()->pluck('theme')->filter()->toArray();
        $userGroups = User::distinct()->pluck('group')->filter()->toArray();

        $pdf = Pdf::loadView('events.report-pdf', compact('dateFrom', 'dateTo', 'type', 'theme', 'group', 'byType', 'byTheme', 'byGroup', 'byMonth', 'eventTypes', 'eventThemes', 'userGroups'));
        return $pdf->download('attendance_report.pdf');
    }

    public function manageAttendance(Event $event)
    {
        $registrations = Registration::where('event_id', $event->id)
            ->with('user')
            ->get();

        $attendances = Attendance::where('event_id', $event->id)
            ->pluck('attended', 'user_id')
            ->toArray();

        // Получаем группы мероприятия
        $eventGroups = EventGroup::where('event_id', $event->id)->get();

        // Генерируем QR-коды для каждой группы
        $qrCodes = [];
        foreach ($eventGroups as $eventGroup) {
            // Создаем уникальный токен для каждой группы
            $groupToken = $event->attendance_token . '_' . $eventGroup->group;

            // Генерируем QR-код с информацией о группе
            $qr = new QrCode(route('events.attendance.verify', [
                'token' => $groupToken,
                'group' => $eventGroup->group
            ]));
            $qr->setSize(200);
            $writer = new PngWriter();
            $qrCodes[$eventGroup->group] = base64_encode($writer->write($qr)->getString());
        }

        // Если нет групп, создаем общий QR-код
        if (empty($qrCodes) && $event->attendance_token) {
            $qr = new QrCode(route('events.attendance.verify', $event->attendance_token));
            $qr->setSize(200);
            $writer = new PngWriter();
            $qrCodes['default'] = base64_encode($writer->write($qr)->getString());
        }

        return view('events.attendance', compact('event', 'registrations', 'attendances', 'qrCodes'));
    }

    public function storeAttendance(Request $request, Event $event)
    {
        $request->validate([
            'attendance' => 'array',
            'attendance.*' => 'boolean',
        ]);

        DB::beginTransaction();
        try {
            Attendance::where('event_id', $event->id)->delete();

            $attendanceData = $request->input('attendance', []);
            foreach ($attendanceData as $userId => $attended) {
                if ($attended) {
                    Attendance::create([
                        'user_id' => $userId,
                        'event_id' => $event->id,
                        'attended' => true,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('events.show', $event)->with('success', 'Посещаемость успешно обновлена.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Ошибка при сохранении посещаемости: ' . $e->getMessage());
        }
    }

    // Метод register с проверкой доступа по группе
    public function register(Request $request, Event $event)
    {
        $user = Auth::user();

        if ($event->registrations->where('user_id', $user->id)->isNotEmpty()) {
            return redirect()->route('events.show', $event)->with('error', 'Вы уже зарегистрированы на это мероприятие.');
        }

        if ($event->registrations->count() >= $event->max_participants) {
            return redirect()->route('events.show', $event)->with('error', 'Места на мероприятие закончились.');
        }

        if (!$event->date_time->isFuture()) {
            return redirect()->route('events.show', $event)->with('error', 'Регистрация на прошедшие мероприятия невозможна.');
        }

        // Проверка доступа по группе
        if ($user->isStudent() && !$event->isAvailableForGroup($user->group)) {
            return redirect()->route('events.show', $event)->with('error', 'Это мероприятие недоступно для вашей группы.');
        }

        Registration::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'is_attendance_confirmed' => false,
        ]);

        return redirect()->route('events.show', $event)->with('success', 'Вы успешно зарегистрированы на мероприятие.');
    }

    public function unregister(Request $request, Event $event)
    {
        $user = Auth::user();

        $registration = Registration::where('user_id', $user->id)
            ->where('event_id', $event->id)
            ->first();

        if (!$registration) {
            return redirect()->route('events.show', $event)->with('error', 'Вы не зарегистрированы на это мероприятие.');
        }

        if ($registration->is_attendance_confirmed) {
            return redirect()->route('events.show', $event)->with('error', 'Отмена регистрации невозможна после подтверждения посещаемости.');
        }

        $registration->delete();

        return redirect()->route('events.show', $event)->with('success', 'Регистрация успешно отменена.');
    }

    public function verifyAttendance($token)
    {
        // Проверяем, содержит ли токен информацию о группе
        $tokenParts = explode('_', $token);
        $baseToken = $tokenParts[0];
        $groupFromToken = isset($tokenParts[1]) ? $tokenParts[1] : null;

        $event = Event::where('attendance_token', $baseToken)->firstOrFail();

        // Проверяем, авторизован ли пользователь
        if (!Auth::check()) {
            // Сохраняем токен в сессии и перенаправляем на страницу входа
            session(['attendance_token' => $token]);
            return redirect()->route('login')
                ->with('info', 'Пожалуйста, войдите в систему, чтобы отметить посещение мероприятия.');
        }

        $user = Auth::user();

        if ($user->role !== 'student') {
            return redirect()->route('events.index')->with('error', 'Только студенты могут отмечать посещаемость.');
        }

        // Проверяем соответствие группы студента группе из токена
        if ($groupFromToken && $user->group !== $groupFromToken) {
            Log::info('Verify attendance: Group mismatch', [
                'user_group' => $user->group,
                'token_group' => $groupFromToken
            ]);
            // Не выдаем ошибку 403, а просто перенаправляем с сообщением
            return redirect()->route('events.show', $event)
                ->with('error', 'QR-код предназначен для другой группы. Ваша группа: ' . $user->group);
        }

        $eventDate = $event->date_time->startOfDay();
        $today = now()->startOfDay();
        $isSameDay = $eventDate->equalTo($today);

        Log::info('Verify attendance: Date check', [
            'event_id' => $event->id,
            'event_date' => $event->date_time->toDateTimeString(),
            'today' => now()->toDateTimeString(),
            'is_today' => $isSameDay,
        ]);

        if (!$isSameDay) {
            return redirect()->route('events.show', $event)->with('error', 'Посещаемость можно отметить только в день мероприятия.');
        }

        // Проверяем, доступно ли мероприятие для группы студента
        if (!$event->isAvailableForGroup($user->group)) {
            Log::info('Verify attendance: Event not available for group', [
                'user_group' => $user->group,
                'event_id' => $event->id
            ]);
            return redirect()->route('events.index')
                ->with('error', 'Это мероприятие не доступно для вашей группы.');
        }

        // Проверяем регистрацию или создаем новую
        $registration = Registration::firstOrCreate(
            ['event_id' => $event->id, 'user_id' => $user->id],
            ['is_attendance_confirmed' => false]
        );

        DB::beginTransaction();
        try {
            Attendance::updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $user->id],
                ['attended' => true]
            );

            $registration->update(['is_attendance_confirmed' => true]);

            DB::commit();
            return redirect()->route('events.show', $event)->with('success', 'Посещаемость отмечена успешно.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('events.show', $event)->with('error', 'Ошибка при отметке посещаемости: ' . $e->getMessage());
        }
    }

    // Отображение QR-кода
    public function showQrCode(Event $event, Request $request)
    {
        $user = Auth::user();

        // Проверка прав доступа - разрешаем и админу, и куратору
        if (!$user->isAdmin() && !$user->isCurator()) {
            abort(403, 'Unauthorized action.');
        }

        $group = $request->query('group', 'default');

        // Генерируем QR-код
        if ($group === 'default') {
            $qrUrl = route('events.attendance.verify', $event->attendance_token);
        } else {
            $groupToken = $event->attendance_token . '_' . $group;
            $qrUrl = route('events.attendance.verify', [
                'token' => $groupToken,
                'group' => $group
            ]);
        }

        $qr = new QrCode($qrUrl);
        $qr->setSize(300);
        $writer = new PngWriter();
        $qrCode = base64_encode($writer->write($qr)->getString());

        return view('events.qrcode', compact('event', 'qrCode', 'group'));
    }

    // Скачивание QR-кода
    public function downloadQrCode(Event $event, Request $request)
    {
        $user = Auth::user();

        // Проверка прав доступа - разрешаем и админу, и куратору
        if (!$user->isAdmin() && !$user->isCurator()) {
            abort(403, 'Unauthorized action.');
        }

        $group = $request->query('group', 'default');

        // Генерируем QR-код
        if ($group === 'default') {
            $qrUrl = route('events.attendance.verify', $event->attendance_token);
        } else {
            $groupToken = $event->attendance_token . '_' . $group;
            $qrUrl = route('events.attendance.verify', [
                'token' => $groupToken,
                'group' => $group
            ]);
        }

        $qr = new QrCode($qrUrl);
        $qr->setSize(300);
        $writer = new PngWriter();

        $fileName = 'qrcode_' . $event->id;
        if ($group !== 'default') {
            $fileName .= '_' . $group;
        }
        $fileName .= '.png';

        return response($writer->write($qr)->getString())
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
    }

    public function manualAttendanceForm()
    {
        return view('events.manual-attendance');
    }

    public function manualAttendance(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
        ]);

        $token = trim($request->input('token'));
        $event = Event::where('attendance_token', $token)->first();

        if (!$event) {
            $availableTokens = Event::pluck('attendance_token')->toArray();
            Log::error('Manual attendance: Invalid token', [
                'input_token' => $token,
                'input_length' => strlen($token),
                'available_tokens' => $availableTokens,
            ]);
            return redirect()->route('events.manual.attendance.form')
                ->with('error', 'Неверный токен посещаемости. Проверьте правильность ввода (токен должен быть 32 или 36 символов).');
        }

        $user = Auth::user();

        if ($user->role !== 'student') {
            return redirect()->route('events.manual.attendance.form')
                ->with('error', 'Только студенты могут отмечать посещаемость.');
        }

        if (!$event->date_time->isToday()) {
            return redirect()->route('events.manual.attendance.form')
                ->with('error', 'Посещаемость можно отметить только в день мероприятия.');
        }

        $registration = $event->registrations->where('user_id', $user->id)->first();
        if (!$registration) {
            return redirect()->route('events.manual.attendance.form')
                ->with('error', 'Вы не зарегистрированы на это мероприятие.');
        }

        DB::beginTransaction();
        try {
            Attendance::updateOrCreate(
                ['event_id' => $event->id, 'user_id' => $user->id],
                ['attended' => true]
            );

            $registration->update(['is_attendance_confirmed' => true]);

            DB::commit();
            return redirect()->route('events.show', $event)
                ->with('success', 'Посещаемость отмечена успешно.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('events.manual.attendance.form')
                ->with('error', 'Ошибка при отметке посещаемости: ' . $e->getMessage());
        }
    }
}