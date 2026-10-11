<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Envía a cada usuario a su página principal según el rol. */
class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return redirect()->route('home');
    }
}
