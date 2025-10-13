<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Мероприятие') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if (session('success'))
                        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <h3 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">{{ $event->title }}</h3>
                    <p class="mb-2"><strong>Дата и время:</strong> {{ $event->date_time ? $event->date_time->format('d.m.Y H:i') : 'Не указана' }}</p>
                    <p class="mb-2"><strong>Место:</strong> {{ $event->location }}</p>
                    <p class="mb-2"><strong>Тип:</strong> {{ $event->type ?? 'Не указан' }}</p>
                    <p class="mb-2"><strong>Тематика:</strong> {{ $event->theme ?? 'Не указана' }}</p>
                    <p class="mb-2"><strong>Участников:</strong> {{ $event->registrations->count() }} / {{ $event->max_participants }}</p>
                    <p class="mb-4"><strong>Описание:</strong> {{ $event->description ?? 'Нет описания' }}</p>
<!-- Группы -->
@if (Auth::user()->isAdmin() || Auth::user()->isCurator())
    <div class="mt-4">
        <h3 class="text-lg font-semibold">Группы</h3>
        <div class="mt-2">
            @if ($event->eventGroups->count() > 0)
                <div class="flex flex-wrap gap-2">
                    @foreach ($event->eventGroups as $eventGroup)
                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-800 text-blue-800 dark:text-blue-100 rounded-full text-sm">
                            {{ $eventGroup->group }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-gray-500 dark:text-gray-400">Доступно для всех групп</p>
            @endif
        </div>
    </div>
@endif

           

@if ((auth()->user()->isAdmin() || auth()->user()->isCurator()) && !empty($qrCodes))
    <div class="mb-4">
        <h4 class="text-lg font-semibold mb-2 text-gray-800 dark:text-gray-200">QR-коды для отметки посещаемости</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
            @foreach($qrCodes as $group => $qrCode)
                <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow">
                    <h5 class="text-md font-semibold mb-2">
                        @if($group === 'default')
                            Общий QR-код
                        @else
                            QR-код для группы: {{ $group }}
                        @endif
                    </h5>
                    <div class="flex justify-center mb-4">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code for {{ $group }}">
                    </div>
                    <div class="flex justify-center space-x-2">
                        <a href="{{ route('events.qrcode', ['event' => $event->id, 'group' => $group]) }}"
                           class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition text-sm">
                            Открыть
                        </a>
                        <a href="{{ route('events.qrcode.download', ['event' => $event->id, 'group' => $group]) }}"
                           class="bg-green-500 text-white px-3 py-1 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 transition text-sm">
                            Скачать
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
        
        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">Студенты могут отсканировать QR-код своей группы для отметки посещаемости.</p>
    </div>
@endif

                    @if (auth()->user()->role === 'admin')
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('events.edit', $event) }}"
           class="bg-amber-500 text-white px-4 py-2 rounded hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700 transition">
            Редактировать
        </a>
        <form action="{{ route('events.destroy', $event) }}" method="POST"
              onsubmit="return confirm('Вы уверены, что хотите удалить мероприятие?');">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 transition">
                Удалить
            </button>
        </form>
        <a href="{{ route('events.attendance', $event) }}"
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
            Управление посещаемостью
        </a>
    </div>
@elseif (auth()->user()->role === 'curator')
    <div class="flex flex-wrap gap-4">
        <a href="{{ route('events.attendance', $event) }}"
           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
            Показать QR-коды
        </a>
    </div>
@else
    @if ($event->date_time && $event->date_time->isFuture())
        @if ($event->registrations->where('user_id', auth()->id())->isEmpty())
            <form action="{{ route('events.register', $event) }}" method="POST">
                @csrf
                <button type="submit"
                        class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 transition">
                    Зарегистрироваться
                </button>
            </form>
        @else
            @if (!$event->registrations->where('user_id', auth()->id())->first()->is_attendance_confirmed)
                <form action="{{ route('events.unregister', $event) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 transition">
                        Отменить регистрацию
                    </button>
                </form>
            @endif
        @endif
    @endif
@endif

                    <div class="mt-4">
                        <a href="{{ route('events.index') }}"
                           class="text-blue-500 hover:underline dark:text-blue-400 dark:hover:underline">Назад к списку мероприятий</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>