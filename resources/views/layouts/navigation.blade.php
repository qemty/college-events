<nav x-data="{ open: false }" class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('dashboard') }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                        {{ __('Главная') }}
                    </x-nav-link>
                    <x-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
                        {{ __('Мероприятия') }}
                    </x-nav-link>
                        @if (Auth::user()->isAdmin() || Auth::user()->isCurator())
                            <x-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
                                {{ __('Отчеты') }}
                            </x-nav-link>
                        @endif
                    <x-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')">
                        {{ __('Справка') }}
                    </x-nav-link>
                    @if (Auth::user()->isAdmin())
                        <x-nav-link :href="route('users.index')" :active="request()->routeIs('users.*')">
                            {{ __('Пользователи') }}
                        </x-nav-link>
                        <x-nav-link :href="route('invitations.index')" :active="request()->routeIs('invitations.*')">
                            {{ __('Приглашения') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <!-- Search Bar, Settings Dropdown, and Theme Toggle -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                <!-- Заменить блок с формой поиска на следующий код -->

<!-- Search Bar -->
<form method="GET" action="{{ $searchAction ?? route('events.index') }}" class="flex items-center mr-4">
    <input type="text" name="search" value="{{ request('search') }}"
           placeholder="{{ $searchPlaceholder ?? 'Поиск мероприятий...' }}"
           class="bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 h-10 px-4 w-64">
    <button type="submit" class="ml-2 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
        </svg>
    </button>
</form>
                <!-- Settings Dropdown -->
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-300 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-100 focus:outline-none transition ease-in-out duration-150">
                            <div>{{ Auth::user()->name }}</div>
                            <div class="ms-1">
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </div>
                        </button>
                    </x-slot>

                    <x-slot name="content">
                        <x-dropdown-link :href="route('profile.edit')">
                            {{ __('Профиль') }}
                        </x-dropdown-link>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <x-dropdown-link :href="route('logout')"
                                             onclick="event.preventDefault(); this.closest('form').submit();">
                                {{ __('Выйти') }}
                            </x-dropdown-link>
                        </form>
                    </x-slot>
                </x-dropdown>

                <!-- Theme Toggle -->
                <div class="ml-4">
                    @include('components.theme-toggle')
                </div>
            </div>

            <!-- Hamburger -->
            <div class="-me-2 flex items-center sm:hidden">
                <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-300 hover:text-gray-500 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-700 focus:text-gray-500 dark:focus:text-gray-200 transition duration-150 ease-in-out">
                    <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{'block': open, 'hidden': ! open}" class="hidden sm:hidden">
        <!-- Search Bar at the Top -->
        <div class="px-4 pt-4 pb-2">
    <form method="GET" action="{{ $searchAction ?? route('events.index') }}">
        <div class="flex items-center">
            <input type="text" name="search" value="{{ request('search') }}"
                   placeholder="{{ $searchPlaceholder ?? 'Поиск мероприятий...' }}"
                   class="bg-gray-100 dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 dark:focus:ring-blue-400 dark:focus:border-blue-400 h-10 px-4 w-full">
            <button type="submit" class="ml-2 text-gray-600 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                </svg>
            </button>
        </div>
    </form>
</div>

        <!-- Navigation Links -->
<div class="pt-2 pb-3 space-y-1">
    <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
        {{ __('Главная') }}
    </x-responsive-nav-link>
    
    <!-- Вкладка Мероприятия для всех пользователей -->
    <x-responsive-nav-link :href="route('events.index')" :active="request()->routeIs('events.*')">
        {{ __('Мероприятия') }}
    </x-responsive-nav-link>
    
    <!-- Управление пользователями (только для администраторов) -->
    @if (Auth::user()->isAdmin())
        <x-responsive-nav-link :href="route('users.index')" :active="request()->routeIs('users.index')">
            {{ __('Пользователи') }}
        </x-responsive-nav-link>
    @endif
    
    <!-- Отчеты (только для администраторов и кураторов) -->
    @if (Auth::user()->isAdmin() || Auth::user()->isCurator())
        <x-responsive-nav-link :href="route('reports.index')" :active="request()->routeIs('reports.*')">
            {{ __('Отчеты') }}
        </x-responsive-nav-link>
    @endif
    
    <!-- Справка для всех пользователей -->
    <x-responsive-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')">
        {{ __('Справка') }}
    </x-responsive-nav-link>
</div>

        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}</div>
                <div class="font-medium text-sm text-gray-500 dark:text-gray-400">{{ Auth::user()->email }}</div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')" :active="request()->routeIs('profile.edit')">
                    {{ __('Профиль') }}
                </x-responsive-nav-link>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                                           onclick="event.preventDefault(); this.closest('form').submit();"
                                           :active="false">
                        {{ __('Выйти') }}
                    </x-responsive-nav-link>
                </form>
                <!-- Theme Toggle in Mobile Menu -->
                <div class="px-4 py-2">
                    @include('components.theme-toggle')
                </div>
            </div>
            <!-- Справка -->
<x-nav-link :href="route('help.index')" :active="request()->routeIs('help.*')">
    {{ __('Справка') }}
</x-nav-link>

        </div>
    </div>
</nav>