<?php

namespace App\Http\Controllers\Admin;

use App\Enums\Role;
use App\Enums\UserStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreManagerRequest;
use App\Http\Requests\ReasonRequest;
use App\Models\Property;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * F-24: kelola pengguna. Pengelola hanya melihat penyewa di gedungnya (FR-USER-04).
 */
class UserController extends Controller
{
    public function __construct(private UserService $users) {}

    public function index(Request $request): View
    {
        $actor = $request->user();
        $this->authorize('viewAny', User::class);

        $tabs = $actor->isOwner() ? ['pending', 'tenants', 'managers'] : ['tenants'];
        $tab = in_array($request->query('tab'), $tabs, true) ? $request->query('tab') : $tabs[0];

        $users = $this->scoped($actor, $tab)
            ->with(['instance', 'activeLease.room.property', 'managedProperties'])
            ->when($request->filled('q'), fn (Builder $q) => $q->where(fn ($w) => $w
                ->where('name', 'like', '%'.$request->q.'%')
                ->orWhere('email', 'like', '%'.$request->q.'%')
                ->orWhere('phone', 'like', '%'.$request->q.'%')))
            ->when($tab === 'tenants' && $request->filled('status'), fn ($q) => $q->where('status', $request->status))
            ->orderByRaw('case when status = ? then 0 else 1 end', [UserStatus::Pending->value])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'tab' => $tab,
            'tabs' => $tabs,
            'counts' => collect($tabs)->mapWithKeys(fn ($t) => [$t => $this->scoped($actor, $t)->count()]),
            'properties' => $actor->isOwner() ? Property::orderBy('name')->get(['id', 'name', 'manager_id']) : collect(),
        ]);
    }

    public function show(Request $request, User $user): View
    {
        $this->authorize('view', $user);

        $actor = $request->user();
        $user->load(['instance', 'managedProperties']);

        return view('admin.users.show', [
            'user' => $user,
            'leases' => $user->leases()->forManager($actor)->with('room.property')->latest('start_date')->get(),
            'bookings' => $user->bookings()->forManager($actor)->with('room.property')->latest()->limit(10)->get(),
        ]);
    }

    public function storeManager(StoreManagerRequest $request): RedirectResponse
    {
        $manager = $this->users->createManager($request->safe()->except('properties'), $request->input('properties', []), $request->user());

        return redirect()->route('admin.users.index', ['tab' => 'managers'])->with('success', 'Akun pengelola '.$manager->name.' dibuat.');
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $this->users->approve($user, $request->user());

        return back()->with('success', 'Akun '.$user->name.' disetujui. Penyewa sekarang bisa mengajukan sewa.');
    }

    public function reject(ReasonRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $this->users->reject($user, $request->validated('reason'), $request->user());

        return back()->with('success', 'Pendaftaran '.$user->name.' ditolak.');
    }

    public function deactivate(ReasonRequest $request, User $user): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $this->users->deactivate($user, $request->validated('reason'), $request->user());

        return back()->with('success', 'Akun '.$user->name.' dinonaktifkan.');
    }

    public function reactivate(Request $request, User $user): RedirectResponse
    {
        $this->authorize('manage', User::class);
        $this->users->reactivate($user, $request->user());

        return back()->with('success', 'Akun '.$user->name.' aktif kembali.');
    }

    private function scoped(User $actor, string $tab): Builder
    {
        $query = User::query();

        return match ($tab) {
            'pending' => $query->role(Role::Tenant->value)->where('status', UserStatus::Pending),
            'managers' => $query->role(Role::Manager->value)->withCount('managedProperties'),
            default => $actor->isOwner()
                ? $query->role(Role::Tenant->value)
                : $query->role(Role::Tenant->value)->where(fn (Builder $q) => $q
                    ->whereHas('bookings.room', fn ($r) => $r->whereIn('property_id', $actor->managedPropertyIds()))
                    ->orWhereHas('leases.room', fn ($r) => $r->whereIn('property_id', $actor->managedPropertyIds()))),
        };
    }
}
