<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Информация о приглашении') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-4">Детали приглашения</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Роль:</p>
                                <p class="font-medium">{{ ucfirst($invitation->role) }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Группа:</p>
                                <p class="font-medium">{{ $invitation->group ?? 'Не указана' }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Создано:</p>
                                <p class="font-medium">{{ $invitation->created_at->format('d.m.Y H:i') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Истекает:</p>
                                <p class="font-medium">{{ $invitation->expires_at->format('d.m.Y H:i') }}</p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Статус:</p>
                                <p class="font-medium">
                                    @if ($invitation->used)
                                        <span class="px-2 py-1 bg-gray-200 dark:bg-gray-600 rounded text-sm">Использовано</span>
                                    @elseif ($invitation->isExpired())
                                        <span class="px-2 py-1 bg-red-200 dark:bg-red-800 rounded text-sm">Истекло</span>
                                    @else
                                        <span class="px-2 py-1 bg-green-200 dark:bg-green-800 rounded text-sm">Активно</span>
                                    @endif
                                </p>
                            </div>
                            
                            <div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">Создатель:</p>
                                <p class="font-medium">{{ $invitation->creator->name }}</p>
                            </div>
                        </div>
                    </div>
                    
                    @if (!$invitation->used && !$invitation->isExpired())
                        <div class="mb-6 p-4 bg-gray-100 dark:bg-gray-700 rounded-lg">
                            <h4 class="font-semibold mb-2">Ссылка для регистрации</h4>
                            <div class="flex items-center">
                                <input type="text" value="{{ $registrationUrl }}" readonly class="flex-1 p-2 border rounded-l dark:bg-gray-800 dark:border-gray-600" id="registrationUrl">
                                <button onclick="copyToClipboard()" class="px-4 py-2 bg-blue-500 text-white rounded-r hover:bg-blue-600">
                                    Копировать
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2">
                                Отправьте эту ссылку пользователю для регистрации.
                            </p>
                        </div>
                    @endif
                    
                    <div class="flex items-center justify-between mt-6">
                        <a href="{{ route('invitations.index') }}" class="text-blue-500 hover:underline">
                            &larr; Вернуться к списку
                        </a>
                        
                        @if (!$invitation->used && !$invitation->isExpired())
                            <form action="{{ route('invitations.destroy', $invitation) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600" onclick="return confirm('Вы уверены?')">
                                    Удалить приглашение
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function copyToClipboard() {
            var copyText = document.getElementById("registrationUrl");
            copyText.select();
            copyText.setSelectionRange(0, 99999);
            document.execCommand("copy");
            alert("Ссылка скопирована в буфер обмена");
        }
    </script>
</x-app-layout>
