<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Ручная отметка посещаемости') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <h3 class="text-2xl font-bold mb-4 text-gray-800 dark:text-gray-200">Введите токен посещаемости</h3>

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

                    <form method="POST" action="{{ route('events.manual.attendance') }}">
                        @csrf
                        <div class="mb-4">
                            <label for="token" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Токен посещаемости</label>
                            <input type="text" name="token" id="token"
                                   class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400 @error('token') border-red-300 dark:border-red-600 @enderror" required>
                            @error('token')
                                <span class="text-red-600 dark:text-red-400 text-sm mt-1">{{ $message }}</span>
                            @enderror
                        </div>
                        <button type="submit"
                                class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600 dark:bg-blue-600 dark:hover:bg-blue-700 transition">
                            Отметить посещаемость
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>