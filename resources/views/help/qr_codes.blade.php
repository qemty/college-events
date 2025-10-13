<!-- resources/views/help/qr_codes.blade.php -->
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Справка: QR-коды') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-lg font-semibold mb-4">Использование QR-кодов для отметки посещаемости</h3>
                    
                    <div class="space-y-4">
                        <p>QR-коды используются для быстрой и удобной отметки посещаемости студентов на мероприятиях.</p>
                        
                        <h4 class="text-md font-medium mt-4">Для администраторов:</h4>
                        <ol class="list-decimal list-inside ml-4 space-y-2">
                            <li>Создайте мероприятие в системе</li>
                            <li>Перейдите на страницу мероприятия</li>
                            <li>Нажмите на кнопку "QR-код" для генерации и отображения QR-кода</li>
                            <li>Вы можете скачать QR-код для печати или демонстрации на экране</li>
                            <li>Студенты сканируют QR-код для отметки своего присутствия</li>
                        </ol>
                        
                        <h4 class="text-md font-medium mt-4">Для студентов:</h4>
                        <ol class="list-decimal list-inside ml-4 space-y-2">
                            <li>Зарегистрируйтесь на мероприятие заранее</li>
                            <li>Придя на мероприятие, отсканируйте QR-код с помощью камеры вашего смартфона</li>
                            <li>Подтвердите свое присутствие в открывшемся окне</li>
                            <li>Ваше посещение будет автоматически зарегистрировано в системе</li>
                        </ol>
                        
                        <div class="bg-blue-50 dark:bg-blue-900 p-4 rounded-md mt-6">
                            <p class="text-gray-800 dark:text-gray-200">
                                <strong>Примечание:</strong> QR-коды генерируются уникальными для каждого мероприятия и действительны только в течение времени проведения мероприятия.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
