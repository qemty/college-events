<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Мероприятия') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Уведомления -->
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4 dark:bg-green-900 dark:border-green-700 dark:text-green-200">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4 dark:bg-red-900 dark:border-red-700 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            <div class="flex flex-col lg:flex-row gap-6">
                <!-- Левая колонка: Фильтры и сортировка -->
                <div class="lg:w-1/4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <h3 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">Фильтры</h3>
                    <form method="GET" class="space-y-6">
                        <input type="hidden" name="tab" value="{{ $tab }}">


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

                        <!-- Комбинированный фильтр по группам с чекбоксами внутри выпадающего списка -->
                        @if(Auth::user()->isAdmin() || Auth::user()->isCurator())
                        <div>
                            <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Группы</h4>
                            
                            <!-- Поиск по группам -->
                            <div class="mb-2">
                                <input type="text" id="group-search" placeholder="Поиск группы..." 
                                       class="w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400">
                            </div>
                            
                            <!-- Выпадающий список с чекбоксами -->
                            <div class="relative">
                                <button type="button" id="dropdown-button" 
                                        class="w-full bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm px-4 py-2 text-left flex justify-between items-center focus:outline-none focus:ring-2 focus:ring-blue-500 dark:focus:ring-blue-400">
                                    <span id="selected-count">Выберите группы</span>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>
                                
                                <div id="dropdown-menu" class="absolute z-10 w-full mt-1 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md shadow-lg hidden">
                                    <div class="p-2 border-b border-gray-200 dark:border-gray-600">
                                        <label class="flex items-center">
                                            <input type="checkbox" id="select-all-groups" 
                                                   class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400">
                                            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">Выбрать все</span>
                                        </label>
                                    </div>
                                    <div class="max-h-60 overflow-y-auto p-2" id="group-checkboxes">
                                        @foreach ($allGroups as $group)
                                            <label class="flex items-center py-1 group-item">
                                                <input type="checkbox" name="groups[]" value="{{ $group }}" {{ in_array($group, $groups) ? 'checked' : '' }}
                                                       class="h-4 w-4 text-blue-600 dark:text-blue-500 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-400 group-checkbox">
                                                <span class="ml-2 text-sm text-gray-600 dark:text-gray-400 group-name">{{ $group }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <div class="p-2 border-t border-gray-200 dark:border-gray-600 flex justify-between">
                                        <button type="button" id="apply-groups" class="text-sm text-blue-600 dark:text-blue-400 hover:underline">Применить</button>
                                        <button type="button" id="clear-groups" class="text-sm text-red-600 dark:text-red-400 hover:underline">Очистить</button>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Выбранные группы (бейджи) -->
                            <div id="selected-groups" class="mt-2 flex flex-wrap gap-1">
                                <!-- Здесь будут отображаться выбранные группы -->
                            </div>
                        </div>
                        @endif

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
                            <a href="{{ route('events.index') }}"
                               class="text-blue-600 hover:underline dark:text-blue-500 dark:hover:underline text-center">Сбросить</a>
                        </div>
                    </form>
                </div>

                <!-- Правая колонка: Контент -->
                <div class="lg:w-3/4 bg-white dark:bg-gray-800 p-6 rounded-lg shadow-sm">
                    <!-- Вкладки -->
                    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
                        <nav class="flex space-x-4">
                            <a href="{{ route('events.index', array_merge(request()->query(), ['tab' => 'upcoming'])) }}"
                               class="px-3 py-2 {{ $tab === 'upcoming' ? 'border-b-2 border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-500' : 'text-gray-600 dark:text-gray-400' }} hover:text-blue-600 dark:hover:text-blue-500">
                                Предстоящие
                            </a>
                            <a href="{{ route('events.index', array_merge(request()->query(), ['tab' => 'past'])) }}"
                               class="px-3 py-2 {{ $tab === 'past' ? 'border-b-2 border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-500' : 'text-gray-600 dark:text-gray-400' }} hover:text-blue-600 dark:hover:text-blue-500">
                                Прошедшие
                            </a>
                            @if (auth()->user()->role === 'student')
                                <a href="{{ route('events.index', array_merge(request()->query(), ['tab' => 'my'])) }}"
                                   class="px-3 py-2 {{ $tab === 'my' ? 'border-b-2 border-blue-600 text-blue-600 dark:border-blue-500 dark:text-blue-500' : 'text-gray-600 dark:text-gray-400' }} hover:text-blue-600 dark:hover:text-blue-500">
                                    Мои мероприятия
                                </a>
                            @endif
                        </nav>
                    </div>

                    <!-- Список мероприятий -->
                    @if ($events->isEmpty())
                        <p class="text-gray-500 dark:text-gray-400">Мероприятий не найдено.</p>
                    @else
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach ($events as $event)
                                <div class="{{ $event->date_time->isPast() ? 'opacity-70 bg-gray-200 dark:bg-gray-600' : 'bg-gray-50 dark:bg-gray-700' }} p-4 rounded-lg shadow-sm hover:shadow-md transition">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ $event->title }}</h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">{{ $event->date_time->format('d.m.Y H:i') }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Место: {{ $event->location }}</p>
                                    <p class="text-sm text-gray-600 dark:text-gray-400">Участников: {{ $event->registrations->count() }} / {{ $event->max_participants }}</p>
                                    @if ($tab === 'my' && auth()->user()->role === 'student')
                                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                            Статус:
                                            @if ($event->registrations->where('user_id', auth()->user()->id)->first()?->is_attendance_confirmed)
                                                Посещено
                                            @elseif ($event->registrations->where('user_id', auth()->user()->id)->isNotEmpty())
                                                Зарегистрирован
                                            @else
                                                Не зарегистрирован
                                            @endif
                                        </p>
                                    @endif
                                    <div class="mt-4 flex flex-wrap gap-2">
                                        <a href="{{ route('events.show', $event) }}"
                                           class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition text-sm">
                                            Подробности
                                        </a>
                                        @if (auth()->user()->role === 'admin')
                                            <a href="{{ route('events.edit', $event) }}"
                                               class="bg-amber-500 text-white px-2 py-1 rounded hover:bg-amber-600 dark:bg-amber-600 dark:hover:bg-amber-700 transition text-sm">
                                                Редактировать
                                            </a>
                                            <form action="{{ route('events.destroy', $event) }}" method="POST"
                                                  onsubmit="return confirm('Вы уверены?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 transition text-sm">
                                                    Удалить
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Пагинация -->
                        <div class="mt-6">
                            {{ $events->appends(request()->query())->links() }}
                        </div>
                    @endif

                    <!-- Кнопка для создания мероприятия (для админа) -->
                    @if (auth()->user()->role === 'admin')
                        <div class="mt-6">
                            <a href="{{ route('events.create') }}"
                               class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 dark:bg-green-700 dark:hover:bg-green-900 transition">
                                Создать мероприятие
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript для работы комбинированного фильтра -->
    @if(Auth::user()->isAdmin() || Auth::user()->isCurator())
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const groupSearch = document.getElementById('group-search');
        const dropdownButton = document.getElementById('dropdown-button');
        const dropdownMenu = document.getElementById('dropdown-menu');
        const selectAllGroups = document.getElementById('select-all-groups');
        const selectedGroupsContainer = document.getElementById('selected-groups');
        const applyGroupsButton = document.getElementById('apply-groups');
        const clearGroupsButton = document.getElementById('clear-groups');
        const selectedCountSpan = document.getElementById('selected-count');
        
        // Функция для обновления выбранных групп
        function updateSelectedGroups() {
            selectedGroupsContainer.innerHTML = '';
            const checkedBoxes = document.querySelectorAll('.group-checkbox:checked');
            
            checkedBoxes.forEach(checkbox => {
                const groupName = checkbox.value;
                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center px-2 py-1 bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200 text-xs font-medium rounded-md';
                badge.innerHTML = `${groupName} <button type="button" class="ml-1 text-blue-500 hover:text-blue-700" data-group="${groupName}">&times;</button>`;
                selectedGroupsContainer.appendChild(badge);
                
                // Обработчик для удаления группы
                badge.querySelector('button').addEventListener('click', function() {
                    const groupToRemove = this.getAttribute('data-group');
                    document.querySelector(`.group-checkbox[value="${groupToRemove}"]`).checked = false;
                    updateSelectedGroups();
                    updateSelectedCount();
                });
            });
        }
        
        // Функция для обновления счетчика выбранных групп
        function updateSelectedCount() {
            const checkedBoxes = document.querySelectorAll('.group-checkbox:checked');
            if (checkedBoxes.length === 0) {
                selectedCountSpan.textContent = 'Выберите группы';
            } else {
                selectedCountSpan.textContent = `Выбрано: ${checkedBoxes.length}`;
            }
        }
        
        // Открытие/закрытие выпадающего списка
        dropdownButton.addEventListener('click', function() {
            dropdownMenu.classList.toggle('hidden');
        });
        
        // Закрытие выпадающего списка при клике вне его
        document.addEventListener('click', function(event) {
            if (!dropdownButton.contains(event.target) && !dropdownMenu.contains(event.target)) {
                dropdownMenu.classList.add('hidden');
            }
        });
        
        // Поиск по группам
        groupSearch.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const groupItems = document.querySelectorAll('.group-item');
            
            groupItems.forEach(item => {
                const groupName = item.querySelector('.group-name').textContent.toLowerCase();
                if (groupName.includes(searchTerm)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
        
        // Выбрать все / Снять выбор
        selectAllGroups.addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.group-checkbox');
            checkboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedGroups();
            updateSelectedCount();
        });
        
        // Обработчик изменения чекбоксов
        document.querySelectorAll('.group-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                // Обновление состояния "Выбрать все"
                const allCheckboxes = document.querySelectorAll('.group-checkbox');
                const checkedCheckboxes = document.querySelectorAll('.group-checkbox:checked');
                selectAllGroups.checked = allCheckboxes.length === checkedCheckboxes.length;
            });
        });
        
        // Применить выбор групп
        applyGroupsButton.addEventListener('click', function() {
            updateSelectedGroups();
            updateSelectedCount();
            dropdownMenu.classList.add('hidden');
        });
        
        // Очистить выбор групп
        clearGroupsButton.addEventListener('click', function() {
            document.querySelectorAll('.group-checkbox').forEach(checkbox => {
                checkbox.checked = false;
            });
            selectAllGroups.checked = false;
            updateSelectedGroups();
            updateSelectedCount();
        });
        
        // Инициализация выбранных групп и счетчика
        updateSelectedGroups();
        updateSelectedCount();
    });
    </script>
    @endif
</x-app-layout>
