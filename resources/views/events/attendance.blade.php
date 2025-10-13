<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Управление посещаемостью') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">Управление посещаемостью: {{ $event->title }}</h3>

                    @if (session('success'))
                        <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded mb-4">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded mb-4">
                            {{ session('error') }}
                        </div>
                    @endif

                    <div class="mt-6">
    <h3 class="text-lg font-semibold mb-4">QR-коды для отметки посещаемости</h3>
    
    @if(count($qrCodes) > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($qrCodes as $group => $qrCode)
                <div class="bg-white dark:bg-gray-700 p-4 rounded-lg shadow">
                    <h4 class="text-md font-semibold mb-2">
                        @if($group === 'default')
                            Общий QR-код
                        @else
                            QR-код для группы: {{ $group }}
                        @endif
                    </h4>
                    <div class="flex justify-center mb-4">
                        <img src="data:image/png;base64,{{ $qrCode }}" alt="QR Code for {{ $group }}">
                    </div>
                    <div class="flex justify-center">
                        <a href="{{ route('events.qrcode', ['event' => $event->id, 'group' => $group]) }}"
                           class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
                            Открыть QR-код
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
                    @else
                        <p class="text-red-600 dark:text-red-400 mb-4">QR-код недоступен: отсутствует токен посещаемости.</p>
                    @endif

                    <form method="POST" action="{{ route('events.attendance.store', $event) }}">
                        @csrf
                        @if ($registrations->isEmpty())
                            <p class="text-gray-500 dark:text-gray-400">Нет зарегистрированных участников.</p>
                        @else
                            <div class="overflow-x-auto">
                                <table class="w-full table-auto mt-6">
                                    <thead>
                                        <tr class="bg-gray-200 dark:bg-gray-700">
                                            <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Студент</th>
                                            <th class="px-4 py-2 text-gray-800 dark:text-gray-200">Посетил</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($registrations as $registration)
                                            <tr class="border-b border-gray-200 dark:border-gray-600">
                                                <td class="px-4 py-2 text-gray-900 dark:text-gray-100">{{ $registration->user->name }}</td>
                                                <td class="px-4 py-2">
                                                    <input type="checkbox" name="attendance[{{ $registration->user_id }}]" value="1"
                                                           class="rounded border-gray-300 dark:border-gray-600 text-blue-500 focus:ring-blue-500 dark:focus:ring-blue-400"
                                                           {{ isset($attendances[$registration->user_id]) && $attendances[$registration->user_id] ? 'checked' : '' }}>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-4">
                                <button type="submit"
                                        class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
                                    Сохранить
                                </button>
                            </div>
                        @endif
                    </form>

                    <div class="mt-4">
                        <a href="{{ route('events.show', $event) }}" class="text-blue-500 hover:underline dark:text-blue-400 dark:hover:underline">Назад к мероприятию</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>