<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('QR-код для мероприятия') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">{{ $event->title }}</h3>
                    
                    <div class="mb-6">
                        <p class="mb-2"><strong>Дата и время:</strong> {{ $event->date_time ? $event->date_time->format('d.m.Y H:i') : 'Не указана' }}</p>
                        <p class="mb-2"><strong>Место:</strong> {{ $event->location }}</p>
                        @if($group !== 'default')
                            <p class="mb-4"><strong>Группа:</strong> {{ $group }}</p>
                        @endif
                    </div>
                    
                    <div class="flex flex-col items-center justify-center mb-6">
                        <h4 class="text-lg font-semibold mb-4 text-gray-800 dark:text-gray-200">
                            QR-код для отметки посещаемости
                        </h4>
                        <div class="bg-white p-4 rounded-lg shadow mb-4">
                            <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code" class="mx-auto">
                        </div>
                        <p class="text-sm text-gray-600 dark:text-gray-400 text-center max-w-md">
                            Студенты могут отсканировать этот QR-код для отметки посещаемости.
                            @if($group !== 'default')
                                <br>Этот QR-код предназначен только для группы {{ $group }}.
                            @endif
                        </p>
                    </div>
                    
                    <!-- <div class="flex flex-col items-center justify-center mb-6">
                        @if(Auth::user()->isAdmin() || Auth::user()->isCurator())
                            <div class="mb-4 w-full max-w-md">
                                <button id="showTokenBtn" type="button" 
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition w-full">
                                    Показать токен
                                </button>
                                <div id="tokenContainer" class="hidden mt-2 p-3 bg-gray-100 dark:bg-gray-700 rounded border border-gray-300 dark:border-gray-600">
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-1">Токен для ручного ввода:</p>
                                    <div class="flex items-center">
                                        <code id="tokenValue" class="bg-white dark:bg-gray-800 px-2 py-1 rounded text-sm font-mono break-all">
                                            @if($group === 'default')
                                                {{ $event->attendance_token }}
                                            @else
                                                {{ $event->attendance_token }}_{{ $group }}
                                            @endif
                                        </code>
                                        <button id="copyTokenBtn" type="button" class="ml-2 text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div> -->
                    
                    <div class="flex justify-center space-x-4">
                        <a href="{{ route('events.qrcode.download', ['event' => $event->id, 'group' => $group]) }}"
                           class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600 dark:bg-green-600 dark:hover:bg-green-700 transition">
                            Скачать QR-код
                        </a>
                        <a href="{{ route('events.show', $event) }}"
                           class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 dark:bg-gray-600 dark:hover:bg-gray-700 transition">
                            Вернуться к мероприятию
                        </a>
                    </div>
                    
                    <!-- @if(Auth::user()->isAdmin() || Auth::user()->isCurator())
                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const showTokenBtn = document.getElementById('showTokenBtn');
                            const tokenContainer = document.getElementById('tokenContainer');
                            const copyTokenBtn = document.getElementById('copyTokenBtn');
                            const tokenValue = document.getElementById('tokenValue');
                            
                            showTokenBtn.addEventListener('click', function() {
                                if (tokenContainer.classList.contains('hidden')) {
                                    tokenContainer.classList.remove('hidden');
                                    showTokenBtn.textContent = 'Скрыть токен';
                                } else {
                                    tokenContainer.classList.add('hidden');
                                    showTokenBtn.textContent = 'Показать токен';
                                }
                            });
                            
                            copyTokenBtn.addEventListener('click', function() {
                                const tempTextArea = document.createElement('textarea');
                                tempTextArea.value = tokenValue.textContent.trim();
                                document.body.appendChild(tempTextArea);
                                tempTextArea.select();
                                document.execCommand('copy');
                                document.body.removeChild(tempTextArea);
                                
                                // Показываем уведомление о копировании
                                const originalText = copyTokenBtn.innerHTML;
                                copyTokenBtn.innerHTML = '<span class="text-green-500">Скопировано!</span>';
                                setTimeout(() => {
                                    copyTokenBtn.innerHTML = originalText;
                                }, 2000);
                            });
                        });
                    </script>
                    @endif -->
                </div>
            </div>
        </div>
    </div>
</x-app-layout>