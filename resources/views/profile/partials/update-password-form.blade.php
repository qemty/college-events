<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-200">
            {{ __('Обновить пароль') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Для обеспечения безопасности убедитесь, что в вашей учетной записи используется длинный случайный пароль.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('put')
        <div>
            <x-input-label for="update_password_current_password" :value="__('Текущий пароль')" class="text-gray-700 dark:text-gray-300" />
            <x-text-input id="update_password_current_password" name="current_password" type="password"
                          class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400"
                          autocomplete="current-password" />
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2 text-red-600 dark:text-red-400" />
        </div>
        <div>
            <x-input-label for="update_password_password" :value="__('Новый пароль')" class="text-gray-700 dark:text-gray-300" />
            <x-text-input id="update_password_password" name="password" type="password"
                          class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400"
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2 text-red-600 dark:text-red-400" />
        </div>
        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Подтверждение пароля')" class="text-gray-700 dark:text-gray-300" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password"
                          class="mt-1 block w-full bg-white dark:bg-gray-700 border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-200 rounded-md shadow-sm focus:ring-blue-500 dark:focus:ring-blue-400"
                          autocomplete="new-password" />
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2 text-red-600 dark:text-red-400" />
        </div>
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Сохранить') }}</x-primary-button>
            @if (session('status') === 'password-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                   class="text-sm text-gray-600 dark:text-gray-400">{{ __('Сохранено.') }}</p>
            @endif
        </div>
    </form>
</section>