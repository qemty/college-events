<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                @if(Auth::user()->isStudent())
                    {{ __('app.student_panel') }}
                @elseif(Auth::user()->isAdmin())
                    {{ __('app.admin_panel') }}
                @elseif(Auth::user()->isCurator())
                    {{ __('app.curator_panel') }}
                @endif
            </h2>
            <button id="theme-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                <svg id="theme-icon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path id="sun" fill-rule="evenodd" d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.707.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" clip-rule="evenodd" />
                    <path id="moon" class="hidden" fill-rule="evenodd" d="M12.316 3.051a1 1 0 01.633 1.265 6 6 0 10-7.78 7.78 1 1 0 01-1.898-.898 8 8 0 119.045-9.045 1 1 0 01.633.898z" clip-rule="evenodd" />
                </svg>
            </button>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-6">
                <h3 class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                    Добро пожаловать, {{ Auth::user()->name }}!
                </h3>
                <p class="text-gray-600 dark:text-gray-400">
                    Вы посетили {{ Auth::user()->attendances()->where('attended', true)->count() }} мероприяти{{ Auth::user()->attendances()->where('attended', true)->count() === 1 ? 'е' : 'й' }}.
                </p>
            </div>

            <!-- Фильтры -->
            <form class="mb-8 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-gray-700 dark:text-gray-300">С даты</label>
                        <input type="date" name="date_from" id="date_from" value="{{ request('date_from') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-gray-700 dark:text-gray-300">По дату</label>
                        <input type="date" name="date_to" id="date_to" value="{{ request('date_to') }}"
                               class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Тип</label>
                        <select name="type" id="type" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Все типы</option>
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type }}" {{ request('type') === $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Тематика</label>
                        <select name="theme" id="theme" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            <option value="">Все тематики</option>
                            @foreach ($eventThemes as $theme)
                                <option value="{{ $theme }}" {{ request('theme') === $theme ? 'selected' : '' }}>{{ $theme }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="mt-4 flex justify-end space-x-2">
                    <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">
                        Фильтровать
                    </button>
                    <a href="{{ route('dashboard') }}" class="bg-gray-300 dark:bg-gray-600 text-gray-800 dark:text-gray-200 px-4 py-2 rounded hover:bg-gray-400 dark:hover:bg-gray-700 transition">
                        Сбросить
                    </a>
                </div>
            </form>

            <!-- Мероприятия -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @forelse ($events as $event)
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition">
                        <div class="p-6">
                            <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $event->title }}</h4>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">{{ $event->date_time->format('d.m.Y H:i') }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Место: {{ $event->location }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Тип: {{ $event->type ?? 'Не указан' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Тематика: {{ $event->theme ?? 'Не указана' }}</p>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                Мест: {{ $event->registrations->count() }} / {{ $event->max_participants }}
                            </p>
                            <div class="mt-4 flex space-x-2">
                                <a href="{{ route('events.show', $event) }}"
                                   class="bg-blue-600 text-white px-3 py-1 rounded hover:bg-blue-700 transition">
                                    Подробнее
                                </a>
                                @if ($event->registrations->where('user_id', Auth::id())->isEmpty() && $event->date_time->isFuture() && $event->registrations->count() < $event->max_participants)
                                    <form action="{{ route('events.register', $event) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700 transition">
                                            Записаться
                                        </button>
                                    </form>
                                @elseif ($event->registrations->where('user_id', Auth::id())->isNotEmpty() && !$event->registrations->where('user_id', Auth::id())->first()->is_attendance_confirmed)
                                    <form action="{{ route('events.unregister', $event) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="bg-red-600 text-white px-3 py-1 rounded hover:bg-red-700 transition">
                                            Отменить
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-gray-600 dark:text-gray-400">Мероприятия не найдены.</p>
                @endforelse
            </div>

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $events->appends(request()->query())->links() }}
            </div>
        </div>
    </div>