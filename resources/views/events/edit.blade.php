<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Редактирование мероприятия') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('events.update', $event) }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Название мероприятия</label>
                            <input type="text" name="title" id="title" value="{{ old('title', $event->title) }}"
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('title') border-red-300 dark:border-red-600 @enderror" required>
                            @error('title')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="date_time" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Дата и время</label>
                            <input type="datetime-local" name="date_time" id="date_time" value="{{ old('date_time', $event->date_time->format('Y-m-d\TH:i')) }}"
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('date_time') border-red-300 dark:border-red-600 @enderror" required>
                            @error('date_time')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="location" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Место проведения</label>
                            <input type="text" name="location" id="location" value="{{ old('location', $event->location) }}"
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('location') border-red-300 dark:border-red-600 @enderror" required>
                            @error('location')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="max_participants" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Максимальное количество участников</label>
                            <input type="number" name="max_participants" id="max_participants" value="{{ old('max_participants', $event->max_participants) }}"
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('max_participants') border-red-300 dark:border-red-600 @enderror" required min="1">
                            @error('max_participants')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="type" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Тип мероприятия</label>
                            <select name="type" id="type" 
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('type') border-red-300 dark:border-red-600 @enderror">
                                <option value="">{{ __('Выберите тип мероприятия') }}</option>
                                @foreach($eventTypes as $key => $name)
                                    <option value="{{ $key }}" {{ old('type', $event->type) == $key ? 'selected' : '' }}>{{ $name }}</option>
                                @endforeach
                            </select>
                            @error('type')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="theme" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Тема мероприятия</label>
                            <input type="text" name="theme" id="theme" value="{{ old('theme', $event->theme) }}"
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('theme') border-red-300 dark:border-red-600 @enderror">
                            @error('theme')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Описание</label>
                            <textarea name="description" id="description"
                                      class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('description') border-red-300 dark:border-red-600 @enderror">{{ old('description', $event->description) }}</textarea>
                            @error('description')
                                <p class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <!-- Группы -->
                        <div class="mb-4">
                            <label for="groups" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Группы</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Выберите группы, для которых предназначено мероприятие</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach ($availableGroups as $group)
                                    <div class="flex items-center">
                                        <input type="checkbox" id="group_{{ $loop->index }}" name="groups[]" value="{{ $group }}" 
                                            {{ in_array($group, old('groups', $eventGroups ?? [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="group_{{ $loop->index }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $group }}</label>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('groups')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Добавление новой группы -->
                        <div class="mb-4">
                            <label for="new_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Добавить новую группу</label>
                            <div class="flex">
                                <input type="text" name="new_group" id="new_group" value="{{ old('new_group') }}"
                                       class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-l-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400">
                                <button type="button" id="add_new_group" 
                                        class="mt-1 bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
                                    Добавить
                                </button>
                            </div>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Введите название новой группы или несколько групп через запятую</p>
                            
                            <div id="new_groups_container" class="mt-2"></div>
                        </div>
                        
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const addNewGroupBtn = document.getElementById('add_new_group');
                                const newGroupInput = document.getElementById('new_group');
                                const newGroupsContainer = document.getElementById('new_groups_container');
                                const groupsContainer = document.querySelector('.grid');
                                
                                addNewGroupBtn.addEventListener('click', function() {
                                    const groupValue = newGroupInput.value.trim();
                                    if (!groupValue) return;
                                    
                                    // Разделяем ввод по запятым, если есть
                                    const groups = groupValue.split(',').map(g => g.trim()).filter(g => g);
                                    
                                    groups.forEach(group => {
                                        // Проверяем, существует ли уже такая группа
                                        const existingGroups = Array.from(document.querySelectorAll('input[name="groups[]"]'))
                                            .map(input => input.value);
                                            
                                        if (!existingGroups.includes(group)) {
                                            // Создаем новый элемент группы
                                            const groupIndex = existingGroups.length;
                                            const groupDiv = document.createElement('div');
                                            groupDiv.className = 'flex items-center mt-1';
                                            
                                            // Создаем чекбокс
                                            const checkbox = document.createElement('input');
                                            checkbox.type = 'checkbox';
                                            checkbox.id = 'group_' + groupIndex;
                                            checkbox.name = 'groups[]';
                                            checkbox.value = group;
                                            checkbox.checked = true;
                                            checkbox.className = 'rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50';
                                            
                                            // Создаем метку
                                            const label = document.createElement('label');
                                            label.htmlFor = 'group_' + groupIndex;
                                            label.className = 'ml-2 text-sm text-gray-700 dark:text-gray-300';
                                            label.textContent = group + ' (новая)';
                                            
                                            // Добавляем элементы в DOM
                                            groupDiv.appendChild(checkbox);
                                            groupDiv.appendChild(label);
                                            groupsContainer.appendChild(groupDiv);
                                        }
                                    });
                                    
                                    // Очищаем поле ввода
                                    newGroupInput.value = '';
                                });
                            });
                        </script>
                        
                        <div class="flex space-x-4">
                            <button type="submit"
                                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
                                Обновить
                            </button>
                            <a href="{{ route('events.index') }}"
                               class="text-blue-500 hover:underline dark:text-blue-400 dark:hover:underline">Отмена</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
