<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ReviewRequest;
use App\Models\Lease;
use App\Models\Review;
use App\Services\ReviewService;
use Illuminate\Http\RedirectResponse;

/**
 * F-13: ulasan penyewa per kontrak.
 */
class ReviewController extends Controller
{
    public function __construct(private ReviewService $reviews) {}

    public function store(ReviewRequest $request, Lease $lease): RedirectResponse
    {
        $this->authorize('review', $lease);
        $this->reviews->create($lease, $request->user(), $request->integer('rating'), $request->validated('comment'));

        return redirect()->to(route('app.leases.show', $lease).'#ulasan')->with('success', 'Terima kasih! Ulasanmu sudah tampil di halaman kost.');
    }

    public function update(ReviewRequest $request, Review $review): RedirectResponse
    {
        $this->authorize('update', $review);
        $this->reviews->update($review, $request->integer('rating'), $request->validated('comment'));

        return back()->with('success', 'Ulasan diperbarui.');
    }
}
