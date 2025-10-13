<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Пользователи') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Список пользователей</h3>
                        <a href="{{ route('invitations.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-700 focus:bg-blue-700 active:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                            Создать приглашение
                        </a>
                    </div>

                    <!-- Фильтры -->
                    <div class="mb-6">
                        <form action="{{ route('users.index') }}" method="GET">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
            <x-input-label for="role" :value="__('Роль')" />
            <select id="role" name="role"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                <option value="">Все роли</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Администратор</option>
                <option value="curator" {{ request('role') === 'curator' ? 'selected' : '' }}>Куратор</option>
                <option value="student" {{ request('role') === 'student' ? 'selected' : '' }}>Студент</option>
            </select>
        </div>

        <div>
            <x-input-label for="group" :value="__('Группа')" />
            <select id="group" name="group"
                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 shadow-sm">
                <option value="">Все группы</option>
                @foreach ($groups as $groupOption)
                    <option value="{{ $groupOption }}" {{ request('group') === $groupOption ? 'selected' : '' }}>
                        {{ $groupOption }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="sort" class="block text-sm font-medium mb-1">Сортировка</label>
            <select id="sort" name="sort"
                class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700">
                <option value="name_asc" {{ ($sort ?? '') === 'name_asc' ? 'selected' : '' }}>Имя (А-Я)</option>
                <option value="name_desc" {{ ($sort ?? '') === 'name_desc' ? 'selected' : '' }}>Имя (Я-А)</option>
                <option value="email_asc" {{ ($sort ?? '') === 'email_asc' ? 'selected' : '' }}>Email (А-Я)</option>
                <option value="email_desc" {{ ($sort ?? '') === 'email_desc' ? 'selected' : '' }}>Email (Я-А)</option>
                <option value="role_asc" {{ ($sort ?? '') === 'role_asc' ? 'selected' : '' }}>Роль (А-Я)</option>
                <option value="role_desc" {{ ($sort ?? '') === 'role_desc' ? 'selected' : '' }}>Роль (Я-А)</option>
                <option value="group_asc" {{ ($sort ?? '') === 'group_asc' ? 'selected' : '' }}>Группа (А-Я)</option>
                <option value="group_desc" {{ ($sort ?? '') === 'group_desc' ? 'selected' : '' }}>Группа (Я-А)</option>
            </select>
        </div>

         <!-- Кнопка на всю ширину -->
    <div class="mt-4">
        <button type="submit"
            class="w-full h-10 px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
            Применить
        </button>
</div>

    </div>
</form>

                    </div>
                    <!-- Таблица пользователей -->
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-700 rounded-lg overflow-hidden">
                            <thead class="bg-gray-100 dark:bg-gray-600">
                                <tr>
                                    <th class="px-4 py-2 text-left">Имя</th>
                                    <th class="px-4 py-2 text-left">Email</th>
                                    <th class="px-4 py-2 text-left">Роль</th>
                                    <th class="px-4 py-2 text-left">Группа</th>
                                    <th class="px-4 py-2 text-left">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr
                                        class="border-t dark:border-gray-600 {{ $user->role === 'curator' ? 'bg-blue-50 dark:bg-blue-900' : '' }}">
                                        <td class="px-4 py-2">{{ $user->name }}</td>
                                        <td class="px-4 py-2">{{ $user->email }}</td>
                                        <td class="px-4 py-2">
                                            @if ($user->role === 'admin')
                                                <span
                                                    class="px-2 py-1 bg-red-200 dark:bg-red-800 rounded text-sm">Администратор</span>
                                            @elseif ($user->role === 'curator')
                                                <span
                                                    class="px-2 py-1 bg-blue-200 dark:bg-blue-800 rounded text-sm">Куратор</span>
                                            @else
                                                <span
                                                    class="px-2 py-1 bg-green-200 dark:bg-green-800 rounded text-sm">Студент</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            @if ($user->role === 'curator')
                                                @if (is_array($user->curator_groups) && !empty($user->curator_groups))
                                                    {{ implode(', ', $user->curator_groups) }}
                                                @elseif (is_string($user->curator_groups) && !empty($user->curator_groups))
                                                    {{ $user->curator_groups }}
                                                @else
                                                    Не указана
                                                @endif
                                            @else
                                                {{ $user->group ?? 'Не указана' }}
                                            @endif
                                        </td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('users.edit', $user) }}"
                                                class="text-blue-500 hover:underline mr-2">Редактировать</a>
                                            @if ($user->id !== Auth::id())
                                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:underline"
                                                        onclick="return confirm('Вы уверены, что хотите удалить этого пользователя?')">Удалить</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="px-4 py-2 text-center">Пользователи не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
