<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F-26: moderasi ulasan (owner).
 */
class ReviewController extends Controller
{
    public function index(Request $request): View
    {
        $reviews = Review::query()
            ->with(['user', 'property', 'lease'])
            ->when($request->filled('property'), fn ($q) => $q->where('property_id', $request->integer('property')))
            ->when($request->query('tampil') === '1', fn ($q) => $q->where('is_published', true))
            ->when($request->query('tampil') === '0', fn ($q) => $q->where('is_published', false))
            ->when($request->filled('rating'), fn ($q) => $q->where('rating', $request->integer('rating')))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.reviews.index', [
            'reviews' => $reviews,
            'properties' => Property::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function toggle(Request $request, Review $review, ReviewService $service): RedirectResponse
    {
        $this->authorize('moderate', Review::class);
        $service->toggle($review, $request->user());

        return back()->with('success', $review->is_published ? 'Ulasan ditampilkan kembali.' : 'Ulasan disembunyikan dari halaman publik.');
    }
}
