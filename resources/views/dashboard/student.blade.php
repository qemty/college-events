<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Панель учащегося') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Левая колонка: Фильтры и сортировка -->
                <div class="lg:w-1/4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Фильтры</h3>
                    <form method="GET" action="{{ route('dashboard') }}" class="space-y-6">
                        <!-- Фильтр по дате -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Дата</h4>
                            <div class="space-y-2">
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400">С</label>
                                    <input type="date" name="date_from" value="{{ $dateFrom }}"
                                           class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400">
                                </div>
                                <div>
                                    <label class="block text-sm text-gray-600 dark:text-gray-400">По</label>
                                    <input type="date" name="date_to" value="{{ $dateTo }}"
                                           class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400">
                                </div>
                            </div>
                        </div>

                        <!-- Фильтр по типу -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Тип мероприятия</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto p-1">
                                <label class="flex items-center">
                                    <input type="checkbox" name="types[]" value="All" {{ in_array('All', $types ?? []) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Все</span>
                                </label>
                                @foreach ($eventTypes as $eventType)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="types[]" value="{{ $eventType }}" {{ in_array($eventType, $types ?? []) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $eventType }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Фильтр по тематике -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Тематика</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto p-1">
                                <label class="flex items-center">
                                    <input type="checkbox" name="themes[]" value="All" {{ in_array('All', $themes ?? []) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Все</span>
                                </label>
                                @foreach ($eventThemes as $eventTheme)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="themes[]" value="{{ $eventTheme }}" {{ in_array($eventTheme, $themes ?? []) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $eventTheme }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <!-- Фильтр по группам -->
                        <!-- <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Группы</h4>
                            <div class="space-y-2 max-h-40 overflow-y-auto p-1">
                                <label class="flex items-center">
                                    <input type="checkbox" name="groups[]" value="All" {{ in_array('All', $groups ?? []) ? 'checked' : '' }}
                                           class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400">
                                    <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Все</span>
                                </label>
                                @foreach ($groups as $group)
                                    <label class="flex items-center">
                                        <input type="checkbox" name="groups[]" value="{{ $group }}" {{ in_array($group, $groups ?? []) ? 'checked' : '' }}
                                               class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400">
                                        <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ $group }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div> -->

                        <!-- Сортировка -->
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Сортировка</h4>
                            <select name="sort"
                                    class="block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400">
                                <option value="date_time_asc" {{ $sort === 'date_time_asc' ? 'selected' : '' }}>По дате (возрастание)</option>
                                <option value="date_time_desc" {{ $sort === 'date_time_desc' ? 'selected' : '' }}>По дате (убывание)</option>
                                <option value="title_asc" {{ $sort === 'title_asc' ? 'selected' : '' }}>По названию (А-Я)</option>
                                <option value="title_desc" {{ $sort === 'title_desc' ? 'selected' : '' }}>По названию (Я-А)</option>
                            </select>
                        </div>

                        <!-- Кнопки -->
                        <div class="flex flex-col space-y-2">
                            <button type="submit"
                                    class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition">
                                Применить
                            </button>
                            <a href="{{ route('dashboard') }}"
                               class="text-blue-600 hover:underline dark:text-blue-500 dark:hover:underline text-center">Сбросить</a>
                        </div>
                    </form>
                </div>

                <!-- Правая колонка: Контент -->
                <div class="lg:w-3/4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <p class="mb-6 text-gray-600 dark:text-gray-400">Добро пожаловать, {{ auth()->user()->name }}!</p>

                    <!-- Статистика -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div class="bg-blue-100 dark:bg-blue-900 p-6 rounded-lg shadow-sm flex items-center space-x-4">
                            <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                            </svg>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Зарегистрировано</h4>
                                <p class="text-2xl text-blue-600 dark:text-blue-400">{{ $registeredEvents->count() }}</p>
                            </div>
                        </div>
                        <div class="bg-green-100 dark:bg-green-900 p-6 rounded-lg shadow-sm flex items-center space-x-4">
                            <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <div>
                                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Посещено</h4>
                                <p class="text-2xl text-green-600 dark:text-green-400">{{ $attendedEvents->count() }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Кнопки действий -->
                    <div class="mb-6 flex space-x-4">
                        <a href="{{ route('events.index') }}"
                           class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition">
                            Просмотреть мероприятия
                        </a>
                        <!-- <a href="{{ route('events.manual.attendance.form') }}"
                           class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 dark:bg-green-500 dark:hover:bg-green-600 transition">
                            Ввести токен посещаемости
                        </a> -->
                    </div>

                    <!-- Зарегистрированные мероприятия -->
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Зарегистрированные мероприятия</h4>
                    @if ($registeredEvents->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400 mb-6">Вы не зарегистрированы ни на одно мероприятие.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                            @foreach ($registeredEvents as $registration)
                                <div class="{{ $registration->event->date_time->isPast() ? 'opacity-70 bg-gray-200 dark:bg-gray-600' : 'bg-gray-50 dark:bg-gray-700' }} p-4 rounded-lg shadow-sm hover:shadow-md transition-all">
                                    <h5 class="text-md font-semibold text-gray-800 dark:text-gray-200">{{ $registration->event->title }}</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $registration->event->date_time->format('d.m.Y H:i') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Место: {{ $registration->event->location }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">
                                        Статус: {{ $registration->is_attendance_confirmed ? 'Посещено' : 'Зарегистрирован' }}
                                    </p>
                                    <div class="mt-2 flex space-x-2">
                                        <a href="{{ route('events.show', $registration->event) }}"
                                           class="text-blue-600 hover:underline dark:text-blue-400 dark:hover:underline">
                                            Подробности
                                        </a>
                                        @if (!$registration->is_attendance_confirmed && $registration->event->date_time->isFuture())
                                            <form action="{{ route('events.unregister', $registration->event) }}" method="POST" onsubmit="return confirm('Отменить регистрацию?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:underline dark:text-red-400 dark:hover:underline">
                                                    Отменить
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <!-- Посещённые мероприятия -->
                    <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Посещённые мероприятия</h4>
                    @if ($attendedEvents->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">Вы не посещали мероприятия.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach ($attendedEvents as $attendance)
                                <div class="{{ $attendance->event->date_time->isPast() ? 'opacity-70 bg-gray-200 dark:bg-gray-600' : 'bg-gray-50 dark:bg-gray-700' }} p-4 rounded-lg shadow-sm hover:shadow-md transition-all">
                                    <h5 class="text-md font-semibold text-gray-800 dark:text-gray-200">{{ $attendance->event->title }}</h5>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $attendance->event->date_time->format('d.m.Y H:i') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Место: {{ $attendance->event->location }}</p>
                                    <div class="mt-2">
                                        <a href="{{ route('events.show', $attendance->event) }}"
                                           class="text-blue-600 hover:underline dark:text-blue-400 dark:hover:underline">
                                            Подробности
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>