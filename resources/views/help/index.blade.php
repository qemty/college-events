<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Справочная система') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Добро пожаловать в справочную систему</h3>
                    
                    <p class="mb-4">
                        Здесь вы найдете информацию о том, как использовать систему управления мероприятиями колледжа.
                        Выберите интересующий вас раздел справки:
                    </p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mt-6">
                        <!-- Справка по роли -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow">
                            <h4 class="text-lg font-medium mb-2">Руководство по вашей роли</h4>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Узнайте о возможностях и функциях, доступных для вашей роли в системе.
                            </p>
                            
                            @if(Auth::user()->isAdmin())
                                <a href="{{ route('help.admin') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Справка для администратора
                                </a>
                            @elseif(Auth::user()->isCurator())
                                <a href="{{ route('help.curator') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Справка для куратора
                                </a>
                            @else
                                <a href="{{ route('help.student') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Справка для студента
                                </a>
                            @endif
                        </div>
                        
                        <!-- Справка по QR-кодам -->
                        <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow">
                            <h4 class="text-lg font-medium mb-2">QR-коды и посещаемость</h4>
                            <p class="text-gray-600 dark:text-gray-400 mb-4">
                                Узнайте, как работает система регистрации посещаемости с помощью QR-кодов.
                            </p>
                            
                            <a href="{{ route('help.qr_codes') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                Подробнее о QR-кодах
                            </a>
                        </div>
                        
                        @if(Auth::user()->isAdmin() || Auth::user()->isCurator())
                            <!-- Справка по отчетам -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow">
                                <h4 class="text-lg font-medium mb-2">Отчеты и аналитика</h4>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">
                                    Узнайте, как формировать и анализировать отчеты по посещаемости.
                                </p>
                                
                                <a href="{{ route('help.reports') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Подробнее об отчетах
                                </a>
                            </div>
                            
                            <!-- Справка по экспорту -->
                            <div class="bg-gray-50 dark:bg-gray-700 p-6 rounded-lg shadow">
                                <h4 class="text-lg font-medium mb-2">Экспорт данных</h4>
                                <p class="text-gray-600 dark:text-gray-400 mb-4">
                                    Узнайте, как экспортировать данные в различных форматах.
                                </p>
                                
                                <a href="{{ route('help.export') }}" class="inline-block px-4 py-2 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Подробнее об экспорте
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
