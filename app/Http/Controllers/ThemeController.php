<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    public function toggle(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark']);
        $user = auth()->user();
        $user->theme = $request->theme;
        $user->save();

        return response()->json(['status' => 'success']);
    }

    public function toggleGuest(Request $request)
    {
        $request->validate(['theme' => 'required|in:light,dark']);
        session(['theme' => $request->theme]);

        return response()->json(['status' => 'success']);
    }
}