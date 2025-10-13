<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Справка для администратора') }}
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
                    
                    <h3 class="text-lg font-semibold mb-4">Руководство администратора</h3>
                    
                    <div class="space-y-8">
                        <section>
                            <h4 class="text-md font-medium mb-2">Управление пользователями</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Как администратор, вы имеете полный доступ к управлению пользователями системы:
                                </p>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Просмотр списка всех пользователей</li>
                                    <li>Редактирование данных пользователей (роль, группа)</li>
                                    <li>Удаление пользователей</li>
                                    <li>Создание приглашений для регистрации кураторов и студентов</li>
                                </ul>
                                <p class="mt-2">
                                    Для управления пользователями перейдите в раздел "Пользователи" в главном меню.
                                </p>
                            </div>
                        </section>
                        
                        <section>
                            <h4 class="text-md font-medium mb-2">Управление мероприятиями</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Вы можете управлять всеми мероприятиями в системе:
                                </p>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Создание новых мероприятий</li>
                                    <li>Редактирование существующих мероприятий</li>
                                    <li>Удаление мероприятий</li>
                                    <li>Назначение мероприятий для конкретных групп</li>
                                    <li>Просмотр QR-кодов для регистрации посещаемости</li>
                                </ul>
                                <p class="mt-2">
                                    Для управления мероприятиями перейдите в раздел "Мероприятия" в главном меню.
                                </p>
                            </div>
                        </section>
                        
                        <section>
                            <h4 class="text-md font-medium mb-2">Отчеты и аналитика</h4>
                            <div class="pl-4 border-l-4 border-gray-300 dark:border-gray-600">
                                <p class="mb-2">
                                    Вы имеете доступ к полной аналитике и отчетам:
                                </p>
                                <ul class="list-disc pl-5 space-y-1">
                                    <li>Отчеты по посещаемости групп</li>
                                    <li>Отчеты по посещаемости отдельных студентов</li>
                                    <li>Фильтрация отчетов по датам, типам и темам мероприятий</li>
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
                                    Для регистрации новых пользователей используйте систему приглашений:
                                </p>
                                <ol class="list-decimal pl-5 space-y-1">
                                    <li>Перейдите в раздел "Пользователи" и нажмите "Создать приглашение"</li>
                                    <li>Выберите роль (куратор или студент)</li>
                                    <li>Для студентов укажите группу</li>
                                    <li>Установите срок действия приглашения</li>
                                    <li>Скопируйте сгенерированную ссылку и отправьте ее пользователю</li>
                                </ol>
                                <p class="mt-2">
                                    Пользователь, перешедший по ссылке, сможет зарегистрироваться с указанной ролью и группой.
                                </p>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
