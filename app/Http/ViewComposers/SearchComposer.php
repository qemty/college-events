<?php

namespace App\Http\ViewComposers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

class SearchComposer
{
    /**
     * Bind data to the view.
     *
     * @param  View  $view
     * @return void
     */
    public function compose(View $view)
    {
        $user = Auth::user();
        $currentRoute = Route::currentRouteName();
        
        $searchAction = route('events.index');
        $searchPlaceholder = 'Поиск мероприятий...';
        
        // Для администратора
        if ($user && $user->isAdmin()) {
            if (strpos($currentRoute, 'users') === 0) {
                $searchAction = route('users.index');
                $searchPlaceholder = 'Поиск пользователей...';
            } elseif (strpos($currentRoute, 'invitations') === 0) {
                $searchAction = route('invitations.index');
                $searchPlaceholder = 'Поиск приглашений...';
            }
        }
        
        $view->with('searchAction', $searchAction);
        $view->with('searchPlaceholder', $searchPlaceholder);
    }
}