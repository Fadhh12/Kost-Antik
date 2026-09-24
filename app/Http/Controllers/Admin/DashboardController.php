<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request): View
    {
        $properties = Property::forManager($request->user())->withCatalogStats()->orderBy('name')->get();

        return view('admin.dashboard', compact('properties'));
    }
}
