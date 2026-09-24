<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\InstanceRequest;
use App\Models\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/**
 * F-23: master instansi asal penyewa (owner).
 */
class InstanceController extends Controller
{
    public function index(): View
    {
        return view('admin.instances.index', [
            'instances' => Instance::withCount('users')->orderBy('name')->paginate(10),
        ]);
    }

    public function store(InstanceRequest $request): RedirectResponse
    {
        Instance::create($request->validated());

        return back()->with('success', 'Instansi ditambahkan.');
    }

    public function update(InstanceRequest $request, Instance $instance): RedirectResponse
    {
        $instance->update($request->validated());

        return back()->with('success', 'Instansi diperbarui.');
    }

    public function destroy(Instance $instance): RedirectResponse
    {
        $instance->delete();

        return back()->with('success', 'Instansi dihapus. Penyewa terkait kini tanpa instansi.');
    }
}
