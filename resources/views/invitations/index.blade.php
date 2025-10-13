<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Приглашения') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-lg font-semibold">Список приглашений</h3>
                        <a href="{{ route('invitations.create') }}" class="px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                            Создать приглашение
                        </a>
                    </div>

                    @if (session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white dark:bg-gray-700 rounded-lg overflow-hidden">
                            <thead class="bg-gray-100 dark:bg-gray-600">
                                <tr>
                                    <th class="px-4 py-2 text-left">Роль</th>
                                    <th class="px-4 py-2 text-left">Группа</th>
                                    <th class="px-4 py-2 text-left">Создано</th>
                                    <th class="px-4 py-2 text-left">Истекает</th>
                                    <th class="px-4 py-2 text-left">Статус</th>
                                    <th scope="col" class="px-6 py-2 text-left  tracking-wider"> {{ __('Тип') }}</th>
                                    <th class="px-4 py-2 text-left">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($invitations as $invitation)
                                    <tr class="border-t dark:border-gray-600">
                                        <td class="px-4 py-2">{{ ucfirst($invitation->role) }}</td>
                                        <td class="px-4 py-2">{{ $invitation->group ?? 'Не указана' }}</td>
                                        <td class="px-4 py-2">{{ $invitation->created_at->format('d.m.Y H:i') }}</td>
                                        <td class="px-4 py-2">{{ $invitation->expires_at->format('d.m.Y H:i') }}</td>
                                        <td class="px-4 py-2">
                                            @if ($invitation->used)
                                                <span class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-sm">Использовано</span>
                                            @elseif ($invitation->isExpired())
                                                <span class="px-2 py-1 bg-red-200 dark:bg-red-800 rounded text-sm">Истекло</span>
                                            @else
                                                <span class="px-2 py-1 bg-green-200 dark:bg-green-800 rounded text-sm">Активно</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
    @if($invitation->multi_use)
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-800 dark:text-green-100">
            {{ __('Многоразовое') }}
        </span>
    @else
        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-100">
            {{ __('Одноразовое') }}
        </span>
    @endif
</td>
                                        <td class="px-4 py-2">
                                            <a href="{{ route('invitations.show', $invitation) }}" class="text-blue-500 hover:underline mr-2">Просмотр</a>
                                            
                                            @if (!$invitation->used && !$invitation->isExpired())
                                                <form action="{{ route('invitations.destroy', $invitation) }}" method="POST" class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-500 hover:underline" onclick="return confirm('Вы уверены?')">Удалить</button>
                                                </form>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-4 py-2 text-center">Приглашения не найдены</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $invitations->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
