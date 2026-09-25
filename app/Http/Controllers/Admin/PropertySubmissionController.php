<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertySubmission;
use App\Services\PropertySubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertySubmissionController extends Controller
{
    public function index(): View
    {
        return view('admin.property-submissions.index', [
            'submissions' => PropertySubmission::query()->latest()->paginate(15),
        ]);
    }

    public function approve(Request $request, PropertySubmission $propertySubmission, PropertySubmissionService $submissions): RedirectResponse
    {
        $property = $submissions->approve($propertySubmission, $request->user());

        return redirect()->route('admin.property-submissions.index')
            ->with('success', "Kost \"{$property->name}\" ditambahkan ke katalog.");
    }

    public function reject(Request $request, PropertySubmission $propertySubmission, PropertySubmissionService $submissions): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'min:5', 'max:255']]);

        $submissions->reject($propertySubmission, $request->user(), $data['reason']);

        return redirect()->route('admin.property-submissions.index')->with('success', 'Pengajuan ditolak.');
    }
}
