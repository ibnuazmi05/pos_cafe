<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class DashboardRedirectController extends Controller
{
    public function __invoke(): RedirectResponse
    {
        if (! Auth::check()) {
            return redirect()->route('login');
        }

        return redirect()->route(Auth::user()->role->dashboardRoute());
    }
}
