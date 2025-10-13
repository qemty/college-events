<x-app-layout>


         <div class="py-12">
             <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                 <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                     <div class="p-6 text-gray-900 dark:text-gray-100">
                         <p class="mb-4 text-gray-600 dark:text-gray-400">Добро пожаловать, {{ auth()->user()->name }}!</p>

                         <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                             <div class="bg-blue-100 dark:bg-blue-900 p-4 rounded-lg shadow-sm">
                                 <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Всего мероприятий</h4>
                                 <p class="text-2xl text-blue-600 dark:text-blue-400">{{ $totalEvents }}</p>
                             </div>
                             <div class="bg-green-100 dark:bg-green-900 p-4 rounded-lg shadow-sm">
                                 <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Всего регистраций</h4>
                                 <p class="text-2xl text-green-600 dark:text-green-400">{{ $totalRegistrations }}</p>
                             </div>
                             <div class="bg-yellow-100 dark:bg-yellow-900 p-4 rounded-lg shadow-sm">
                                 <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Всего посещений</h4>
                                 <p class="text-2xl text-yellow-600 dark:text-yellow-400">{{ $totalAttendance }}</p>
                             </div>
                         </div>

                         <div class="mb-6 flex space-x-4">
                             <a href="{{ route('events.index') }}"
                                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 dark:bg-blue-500 dark:hover:bg-blue-600 transition">
                                 Управление мероприятиями
                             </a>
                         </div>
                     </div>
                 </div>
             </div>
         </div>
     </x-app-layout>