<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Instance;
use App\Models\Review;
use App\Services\CatalogService;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(CatalogService $catalog): View
    {
        return view('public.home', [
            'featured' => $catalog->featured(3),
            'cities' => $catalog->cities(),
            'instances' => Instance::orderBy('name')->limit(6)->pluck('name'),
            'reviews' => Review::published()
                ->where('rating', '>=', 4)
                ->whereHas('property', fn ($q) => $q->active())
                ->with(['user.instance', 'property'])
                ->latest()
                ->limit(3)
                ->get(),
        ]);
    }
}
