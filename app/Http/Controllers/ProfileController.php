<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Instance;
use App\Services\UserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user()->load('instance'),
            'instances' => Instance::orderBy('name')->pluck('name', 'id'),
        ]);
    }

    public function update(ProfileUpdateRequest $request, UserService $users): RedirectResponse
    {
        $users->updateProfile(
            $request->user(),
            $request->safe()->except(['photo', 'remove_photo']),
            $request->file('photo'),
            $request->boolean('remove_photo'),
        );

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}
