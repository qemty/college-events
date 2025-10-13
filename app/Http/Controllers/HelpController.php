<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HelpController extends Controller
{
    // Конструктор перемещен в базовый класс Controller или middleware указывается в маршрутах
    // public function __construct()
    // {
    //     $this->middleware('auth');
    // }

    // Главная страница справки
    public function index()
    {
        $user = Auth::user();
        $role = $user->role;
        
        return view('help.index', compact('role'));
    }

    // Справка для администраторов
    public function admin()
    {
        if (!Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('help.admin');
    }

    // Справка для кураторов
    public function curator()
    {
        if (!Auth::user()->isCurator() && !Auth::user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('help.curator');
    }

    // Справка для студентов
    public function student()
    {
        return view('help.student');
    }

    // Справка по QR-кодам
    public function qrCodes()
    {
        return view('help.qr_codes');
    }

    // Справка по отчетам
    public function reports()
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isCurator()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('help.reports');
    }

    // Справка по экспорту данных
    public function export()
    {
        if (!Auth::user()->isAdmin() && !Auth::user()->isCurator()) {
            abort(403, 'Unauthorized action.');
        }
        
        return view('help.export');
    }
}
