<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardRedirectController extends Controller
{
    /**
     * FR-AUTH-04: owner/manager ke /admin, tenant ke /app.
     */
    public function __invoke(Request $request): RedirectResponse
    {
        return $request->user()->isStaff()
            ? redirect()->route('admin.dashboard')
            : redirect()->route('app.dashboard');
    }
}
