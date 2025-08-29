<?php

// app/Http/Controllers/ThemeController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ThemeController extends Controller
{
    // app/Http/Controllers/ThemePreferenceController.php
	public function setColor(Request $request)
    {
        $data = $request->validate([
            'pColor' => ['required','regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
            'sColor' => ['required','regex:/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/'],
        ]);

        // persist to session
        session([
            'primary_color' => $data['pColor'],
            'sec_color'     => $data['sColor'],
            'theme'         => session('theme','light'), // keep current
        ]);

        // persist to DB if you want
        if ($request->user()) {
            $request->user()->update([
                'primary_color'   => $data['pColor'],
                'secondary_color' => $data['sColor'],
            ]);
        }

        return response()->json(['ok' => true]);
    }

    public function saveTheme(Request $request)
    {
        $theme = $request->string('theme_color')->toString(); // 'light' | 'dark'
        abort_unless(in_array($theme, ['light','dark']), 422);

        session(['theme' => $theme]);

        if ($request->user()) {
            $request->user()->update(['theme' => $theme]);
        }

        return response()->json(['ok' => true, 'theme' => $theme]);
    }
}

