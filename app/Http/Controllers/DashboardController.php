<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Event;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->role === 'admin' || $user->role === 'curator') {
            $totalEvents = Event::count();
            $totalRegistrations = Registration::count();
            $totalAttendance = Attendance::where('attended', true)->count();

            return view('dashboard.admin', compact('totalEvents', 'totalRegistrations', 'totalAttendance'));
        }

        $search = $request->input('search');
        $sort = $request->input('sort', 'date_time_asc');
        $dateFrom = $request->input('date_from');
        $dateTo = $request->input('date_to');
        $types = $request->input('types', []);
        $themes = $request->input('themes', []);
        $groups = $request->input('groups', []);

        // Зарегистрированные мероприятия
        $query = Registration::where('user_id', $user->id)->with('event');

        if ($search) {
            $query->whereHas('event', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }
        if ($dateFrom) {
            $query->whereHas('event', function ($q) use ($dateFrom) {
                $q->where('date_time', '>=', $dateFrom);
            });
        }
        if ($dateTo) {
            $query->whereHas('event', function ($q) use ($dateTo) {
                $q->where('date_time', '<=', $dateTo . ' 23:59:59');
            });
        }
        if ($types && !in_array('All', $types)) {
            $query->whereHas('event', function ($q) use ($types) {
                $q->whereIn('type', $types);
            });
        }
        if ($themes && !in_array('All', $themes)) {
            $query->whereHas('event', function ($q) use ($themes) {
                $q->whereIn('theme', $themes);
            });
        }
        if ($groups && !in_array('All', $groups)) {
            $query->whereHas('event.registrations.user', function ($q) use ($groups) {
                $q->whereIn('group', $groups);
            });
        }

        $registeredEvents = $query->get();

        // Сортировка коллекции
        $registeredEvents = $registeredEvents->sortBy(function ($registration) use ($sort) {
            $event = $registration->event;
            if (!$event) return 0;
            switch ($sort) {
                case 'date_time_asc':
                    return $event->date_time->timestamp;
                case 'date_time_desc':
                    return -$event->date_time->timestamp;
                case 'title_asc':
                    return $event->title;
                case 'title_desc':
                    return -$event->title;
                default:
                    return $event->date_time->timestamp;
            }
        })->values();

        // Посещённые мероприятия
        $attendedQuery = Attendance::where('user_id', $user->id)
            ->where('attended', true)
            ->with('event');

        if ($search) {
            $attendedQuery->whereHas('event', function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%');
            });
        }
        if ($dateFrom) {
            $attendedQuery->whereHas('event', function ($q) use ($dateFrom) {
                $q->where('date_time', '>=', $dateFrom);
            });
        }
        if ($dateTo) {
            $attendedQuery->whereHas('event', function ($q) use ($dateTo) {
                $q->where('date_time', '<=', $dateTo . ' 23:59:59');
            });
        }
        if ($types && !in_array('All', $types)) {
            $attendedQuery->whereHas('event', function ($q) use ($types) {
                $q->whereIn('type', $types);
            });
        }
        if ($themes && !in_array('All', $themes)) {
            $attendedQuery->whereHas('event', function ($q) use ($themes) {
                $q->whereIn('theme', $themes);
            });
        }
        if ($groups && !in_array('All', $groups)) {
            $attendedQuery->whereHas('event.registrations.user', function ($q) use ($groups) {
                $q->whereIn('group', $groups);
            });
        }

        $attendedEvents = $attendedQuery->get();

        // Сортировка коллекции
        $attendedEvents = $attendedEvents->sortBy(function ($attendance) use ($sort) {
            $event = $attendance->event;
            if (!$event) return 0;
            switch ($sort) {
                case 'date_time_asc':
                    return $event->date_time->timestamp;
                case 'date_time_desc':
                    return -$event->date_time->timestamp;
                case 'title_asc':
                    return $event->title;
                case 'title_desc':
                    return -$event->title;
                default:
                    return $event->date_time->timestamp;
            }
        })->values();

        // Опции для фильтров (преобразуем в массивы)
        $eventTypes = Event::distinct()->pluck('type')->filter()->values()->toArray();
        $eventThemes = Event::distinct()->pluck('theme')->filter()->values()->toArray();
        $groups = User::distinct()->pluck('group')->filter()->values()->toArray();

        return view('dashboard.student', compact(
            'registeredEvents',
            'attendedEvents',
            'search',
            'sort',
            'dateFrom',
            'dateTo',
            'types',
            'themes',
            'groups',
            'eventTypes',
            'eventThemes',
            'groups'
        ));
    }
}