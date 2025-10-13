<!-- resources/views/help/reports.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Справка: Отчеты') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Работа с отчетами в системе</h3>
                    
                    <div class="space-y-4">
                        <p>Система предоставляет различные типы отчетов для анализа посещаемости мероприятий.</p>
                        
                        <h4 class="text-md font-medium mt-4">Доступные отчеты:</h4>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li><strong>Отчет по группам</strong> - показывает статистику посещаемости по группам студентов</li>
                            <li><strong>Отчет по студентам</strong> - детализированная информация о посещаемости каждого студента</li>
                            <li><strong>Отчет по мероприятиям</strong> - статистика по типам и тематикам мероприятий</li>
                        </ul>
                        
                        <h4 class="text-md font-medium mt-4">Как использовать отчеты:</h4>
                        <ol class="list-decimal list-inside ml-4 space-y-2">
                            <li>Перейдите в раздел "Отчеты" в главном меню</li>
                            <li>Выберите нужный тип отчета</li>
                            <li>Установите параметры фильтрации (период, группа, тип мероприятия и т.д.)</li>
                            <li>Нажмите кнопку "Сформировать отчет"</li>
                            <li>Просмотрите результаты в виде таблицы или графика</li>
                            <li>При необходимости экспортируйте отчет в нужном формате</li>
                        </ol>
                        
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-md mt-6">
                            <p class="text-gray-800 dark:text-gray-200">
                                <strong>Совет:</strong> Для более детального анализа используйте комбинацию фильтров и экспортируйте данные в Excel для дальнейшей обработки.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
