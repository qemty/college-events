<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class RouteController extends Controller
{
    /**
     * Проверка маршрутов на конфликты
     */
    public function checkRoutes()
    {
        $routes = Route::getRoutes();
        $routeList = [];
        
        foreach ($routes as $route) {
            $routeList[] = [
                'method' => implode('|', $route->methods()),
                'uri' => $route->uri(),
                'name' => $route->getName(),
                'action' => $route->getActionName(),
            ];
        }
        
        return view('routes.check', compact('routeList'));
    }
}
