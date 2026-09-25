<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Requests\Public\PropertySubmissionRequest;
use App\Services\PropertySubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PropertySubmissionController extends Controller
{
    public function create(): View
    {
        return view('public.submissions.create');
    }

    public function store(PropertySubmissionRequest $request, PropertySubmissionService $submissions): RedirectResponse
    {
        $submissions->submit($request->safe()->except('photos'), $request->file('photos', []));

        return redirect()->route('kost.submissions.create')
            ->with('success', 'Terima kasih! Data kost kamu akan ditinjau tim kami dan dihubungi lewat WhatsApp dalam 1-2 hari kerja.');
    }
}
