<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Справка для куратора') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="mb-6">
                        <a href="{{ route('help.index') }}" class="text-blue-500 hover:underline">
                            &larr; Назад к справке
                        </a>
                    </div>
                    
                    <h3 class="text-lg font-semibold mb-4">Руководство куратора</h3>
                    
                    <div class="space-y-8">
                        <section>
                            <h4 class="text-md font-medium mb-2">Управление группами</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Как куратор, вы имеете доступ к управлению назначенными вам группами:
                                </p>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Просмотр списка студентов в ваших группах</li>
                                    <li>Отслеживание посещаемости мероприятий студентами</li>
                                    <li>Формирование отчетов по группам</li>
                                </ul>
                                <p class="mt-2">
                                    Для просмотра информации о группах перейдите в раздел "Пользователи" в главном меню.
                                </p>
                            </div>
                        </section>
                        
                        <section>
                            <h4 class="text-md font-medium mb-2">Управление мероприятиями</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Вы можете управлять мероприятиями для ваших групп:
                                </p>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Создание новых мероприятий для ваших групп</li>
                                    <li>Редактирование созданных вами мероприятий</li>
                                    <li>Отслеживание посещаемости мероприятий</li>
                                    <li>Генерация QR-кодов для отметки посещаемости</li>
                                </ul>
                                <p class="mt-2">
                                    Для управления мероприятиями перейдите в раздел "Мероприятия" в главном меню.
                                </p>
                            </div>
                        </section>
                        
                        <section>
                            <h4 class="text-md font-medium mb-2">Работа с QR-кодами</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Для отметки посещаемости студентов используйте QR-коды:
                                </p>
                                <ol class="list-decimal pl-5 space-y-1">
                                    <li>Откройте страницу мероприятия</li>
                                    <li>Нажмите кнопку "QR-код"</li>
                                    <li>Выберите группу, для которой нужен QR-код</li>
                                    <li>Покажите QR-код студентам для сканирования</li>
                                    <li>При необходимости нажмите "Показать токен" для отображения токена для ручного ввода</li>
                                    <li>Вы можете скачать QR-код для печати или отправки</li>
                                </ol>
                                <p class="mt-2">
                                    Студенты могут отсканировать QR-код или ввести токен вручную для отметки посещаемости.
                                </p>
                            </div>
                        </section>
                        
                        <section>
                            <h4 class="text-md font-medium mb-2">Отчеты и аналитика</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Вы имеете доступ к отчетам по вашим группам:
                                </p>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Отчеты по посещаемости групп</li>
                                    <li>Отчеты по посещаемости отдельных студентов</li>
                                    <li>Фильтрация отчетов по датам и типам мероприятий</li>
                                    <li>Экспорт отчетов в различных форматах (CSV, PDF, Excel)</li>
                                </ul>
                                <p class="mt-2">
                                    Для доступа к отчетам перейдите в раздел "Отчеты" в главном меню.
                                </p>
                            </div>
                        </section>
                        
                        <section>
                            <h4 class="text-md font-medium mb-2">Создание приглашений</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Вы можете создавать приглашения для студентов ваших групп:
                                </p>
                                <ol class="list-decimal pl-5 space-y-1">
                                    <li>Перейдите в раздел "Пользователи" и нажмите "Создать приглашение"</li>
                                    <li>Выберите роль "студент"</li>
                                    <li>Укажите группу из списка ваших групп</li>
                                    <li>Установите срок действия приглашения</li>
                                    <li>При необходимости отметьте опцию "Многоразовое" для создания приглашения, которое можно использовать несколько раз</li>
                                    <li>Скопируйте сгенерированную ссылку и отправьте ее студентам</li>
                                </ol>
                                <p class="mt-2">
                                    Студенты, перешедшие по ссылке, смогут зарегистрироваться в указанной группе.
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
