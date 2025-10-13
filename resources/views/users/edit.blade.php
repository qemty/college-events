<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Редактирование пользователя') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form method="POST" action="{{ route('users.update', $user) }}">
                        @csrf
                        @method('PUT')

                        <!-- Имя -->
                        <div class="mb-4">
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Имя</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Email -->
                        <div class="mb-4">
                            <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @error('email')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Роль -->
                        <div class="mb-4">
                            <label for="role" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Роль</label>
                            <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Администратор</option>
                                <option value="curator" {{ old('role', $user->role) === 'curator' ? 'selected' : '' }}>Куратор</option>
                                <option value="student" {{ old('role', $user->role) === 'student' ? 'selected' : '' }}>Студент</option>
                            </select>
                            @error('role')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Группа (для студентов) -->
                        <div class="mb-4" id="group-container">
                            <label for="group" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Группа</label>
                            <select id="group" name="group" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Выберите группу</option>
                                @foreach ($groups as $groupOption)
                                    <option value="{{ $groupOption }}" {{ old('group', $user->group) === $groupOption ? 'selected' : '' }}>{{ $groupOption }}</option>
                                @endforeach
                            </select>
                            @error('group')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Группы куратора (для кураторов) -->
                        <div class="mb-4" id="curator-groups-container" style="{{ old('role', $user->role) !== 'curator' ? 'display: none;' : '' }}">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Группы куратора</label>
                            <div class="mt-2 grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                @foreach ($groups as $groupOption)
                                    <div class="flex items-center">
                                        <input type="checkbox" id="curator_group_{{ $loop->index }}" name="curator_groups[]" value="{{ $groupOption }}" 
                                            {{ in_array($groupOption, is_array($user->curator_groups) ? $user->curator_groups : []) ? 'checked' : '' }}
                                            class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <label for="curator_group_{{ $loop->index }}" class="ml-2 text-sm text-gray-700 dark:text-gray-300">{{ $groupOption }}</label>
                                    </div>
                                @endforeach
                            </div>
                            @error('curator_groups')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center justify-end mt-4">
                            <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-gray-700 mr-4">Отмена</a>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Сохранить
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const roleSelect = document.getElementById('role');
            const groupContainer = document.getElementById('group-container');
            const curatorGroupsContainer = document.getElementById('curator-groups-container');
            
            roleSelect.addEventListener('change', function() {
                if (this.value === 'curator') {
                    groupContainer.style.display = 'none';
                    curatorGroupsContainer.style.display = 'block';
                } else {
                    groupContainer.style.display = 'block';
                    curatorGroupsContainer.style.display = 'none';
                }
            });
            
            // Инициализация при загрузке страницы
            if (roleSelect.value === 'curator') {
                groupContainer.style.display = 'none';
                curatorGroupsContainer.style.display = 'block';
            } else {
                groupContainer.style.display = 'block';
                curatorGroupsContainer.style.display = 'none';
            }
        });
    </script>
</x-app-layout>
