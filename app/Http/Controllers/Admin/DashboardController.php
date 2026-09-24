<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F-15 & F-21: dashboard pengelola/owner (A1).
 */
class DashboardController extends Controller
{
    public function __construct(private DashboardService $dashboard) {}

    public function __invoke(Request $request): View
    {
        return view('admin.dashboard', $this->dashboard->forStaff($request->user()) + [
            // Dipakai untuk tampilan tabel grafik (aksesibilitas, tanpa JS).
            'revenue' => $this->dashboard->revenueSeries($request->user()),
        ]);
    }

    /**
     * JSON untuk grafik pendapatan 6 bulan (satu-satunya endpoint JSON, SDD 3.5).
     */
    public function revenueChart(Request $request): JsonResponse
    {
        return response()->json($this->dashboard->revenueSeries($request->user()));
    }
}
