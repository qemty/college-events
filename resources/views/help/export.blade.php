<!-- resources/views/help/export.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Справка: Экспорт данных') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Экспорт данных из системы</h3>
                    
                    <div class="space-y-4">
                        <p>Система позволяет экспортировать данные в различных форматах для дальнейшего использования и анализа.</p>
                        
                        <h4 class="text-md font-medium mt-4">Доступные форматы экспорта:</h4>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li><strong>CSV</strong> - текстовый формат для импорта в электронные таблицы</li>
                            <li><strong>Excel</strong> - формат Microsoft Excel для детального анализа</li>
                            <li><strong>PDF</strong> - для печати и официальных отчетов</li>
                        </ul>
                        
                        <h4 class="text-md font-medium mt-4">Как экспортировать данные:</h4>
                        <ol class="list-decimal list-inside ml-4 space-y-2">
                            <li>Перейдите в раздел "Отчеты" в главном меню</li>
                            <li>Выберите нужный тип отчета</li>
                            <li>Установите параметры фильтрации</li>
                            <li>Нажмите на одну из кнопок экспорта (CSV, Excel, PDF)</li>
                            <li>Файл будет автоматически загружен на ваше устройство</li>
                        </ol>
                        
                        <h4 class="text-md font-medium mt-4">Типы экспортируемых данных:</h4>
                        <ul class="list-disc list-inside ml-4 space-y-2">
                            <li>Списки мероприятий</li>
                            <li>Отчеты о посещаемости по группам</li>
                            <li>Отчеты о посещаемости по студентам</li>
                            <li>Статистика по типам мероприятий</li>
                            <li>Аналитика по периодам</li>
                        </ul>
                        
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-md mt-6">
                            <p class="text-gray-800 dark:text-gray-200">
                                <strong>Примечание:</strong> Для экспорта в формате Excel и PDF требуется установка соответствующих пакетов в системе.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
