<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Создание приглашения') }}
        </h2>
    </x-slot>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('invitations.store') }}">
                        @csrf
                        <!-- Роль -->
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Роль</label>
                            <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach ($availableRoles as $role)
                                    <option value="{{ $role }}">{{ ucfirst($role) }}</option>
                                @endforeach
                            </select>
                            @error('role')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Группа для студента (показывается только при выборе роли student) -->
                        <div id="student_group_field" class="mb-4">
                            <label for="group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Группа студента</label>
                            <select id="group" name="group" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Выберите группу</option>
                                @foreach ($groups as $group)
                                    <option value="{{ $group }}">{{ $group }}</option>
                                @endforeach
                            </select>
                            @error('group')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Группы для куратора (показывается только при выборе роли curator) -->
                        <div id="curator_groups_field" class="mb-4" style="display: none;">
                            <label for="curator_groups" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Группы куратора</label>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Выберите группы, которые будет курировать пользователь</p>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach ($groups as $group)
                                    <div class="flex items-center">
                                        <input type="checkbox" id="curator_group_{{ $loop->index }}" name="curator_groups[]" value="{{ $group }}" 
                                            {{ in_array($group, old('curator_groups', [])) ? 'checked' : '' }}
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="curator_group_{{ $loop->index }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $group }}</label>
                                    </div>
                                @endforeach
                            </div>
                            
                            @error('curator_groups')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Добавление новой группы (показывается для student и curator) -->
                        <div id="new_group_field" class="mb-4">
                            <label for="new_group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Добавить новую группу</label>
                            <input type="text" name="new_group" id="new_group" value="{{ old('new_group') }}"
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400">
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                <span id="student_group_help">Введите название новой группы</span>
                                <span id="curator_group_help" style="display: none;">Введите названия групп через запятую</span>
                            </p>
                        </div>
                        
                        <!-- Срок действия -->
                        <div class="mb-4">
                            <label for="expires_at" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Срок действия</label>
                            <input type="datetime-local" id="expires_at" name="expires_at" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" value="{{ date('Y-m-d\TH:i', strtotime('+7 days')) }}">
                            @error('expires_at')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <!-- Добавить перед кнопкой отправки формы -->
<div class="mt-4">
    <x-input-label for="multi_use" :value="__('Многоразовое приглашение')" />
    <div class="mt-2">
        <label class="inline-flex items-center">
            <input id="multi_use" type="checkbox" name="multi_use" value="1" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800">
            <span class="ml-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Разрешить многократное использование') }}</span>
        </label>
    </div>
    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
        Если включено, по этой ссылке сможет зарегистрироваться неограниченное количество пользователей до истечения срока действия.
    </p>
</div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('invitations.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">Отмена</a>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Создать приглашение
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <script>
        // Показывать/скрывать поля в зависимости от выбранной роли
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const studentGroupField = document.getElementById('student_group_field');
            const curatorGroupsField = document.getElementById('curator_groups_field');
            const newGroupField = document.getElementById('new_group_field');
            const studentGroupHelp = document.getElementById('student_group_help');
            const curatorGroupHelp = document.getElementById('curator_group_help');
            
            function updateFields() {
                const role = roleSelect.value;
                
                // Для студента показываем выбор одной группы
                if (role === 'student') {
                    studentGroupField.style.display = 'block';
                    curatorGroupsField.style.display = 'none';
                    newGroupField.style.display = 'block';
                    studentGroupHelp.style.display = 'inline';
                    curatorGroupHelp.style.display = 'none';
                }
                // Для куратора показываем выбор нескольких групп
                else if (role === 'curator') {
                    studentGroupField.style.display = 'none';
                    curatorGroupsField.style.display = 'block';
                    newGroupField.style.display = 'block';
                    studentGroupHelp.style.display = 'none';
                    curatorGroupHelp.style.display = 'inline';
                }
                // Для админа не показываем выбор групп
                else {
                    studentGroupField.style.display = 'none';
                    curatorGroupsField.style.display = 'none';
                    newGroupField.style.display = 'none';
                }
            }
            
            // Вызываем функцию при загрузке страницы
            updateFields();
            
            // Вызываем функцию при изменении роли
            roleSelect.addEventListener('change', updateFields);
        });
    </script>
</x-app-layout>
