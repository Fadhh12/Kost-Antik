<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\Gender;
use App\Enums\LeaseStatus;
use App\Enums\Role;
use App\Enums\UserStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * status & status_reason sengaja tidak fillable: hanya diubah lewat service.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'instance_id',
        'photo_path',
        'password',
    ];

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'gender' => Gender::class,
            'status' => UserStatus::class,
        ];
    }

    // Relasi

    public function instance(): BelongsTo
    {
        return $this->belongsTo(Instance::class);
    }

    public function managedProperties(): HasMany
    {
        return $this->hasMany(Property::class, 'manager_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(BookingRequest::class);
    }

    public function leases(): HasMany
    {
        return $this->hasMany(Lease::class);
    }

    public function activeLease(): HasOne
    {
        return $this->hasOne(Lease::class)->where('status', LeaseStatus::Active)->latestOfMany();
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    // Peran

    public function isOwner(): bool
    {
        return $this->hasRole(Role::Owner->value);
    }

    public function isManager(): bool
    {
        return $this->hasRole(Role::Manager->value);
    }

    public function isTenant(): bool
    {
        return $this->hasRole(Role::Tenant->value);
    }

    public function isStaff(): bool
    {
        return $this->hasAnyRole([Role::Owner->value, Role::Manager->value]);
    }

    public function roleLabel(): string
    {
        $role = Role::tryFrom((string) $this->getRoleNames()->first());

        return $role?->label() ?? 'Pengguna';
    }

    // Status akun

    public function isAccepted(): bool
    {
        return $this->status === UserStatus::Accepted;
    }

    public function isPending(): bool
    {
        return $this->status === UserStatus::Pending;
    }

    public function hasActiveLease(): bool
    {
        return $this->leases()->where('status', LeaseStatus::Active)->exists();
    }

    public function hasPendingBooking(): bool
    {
        return $this->bookings()->where('status', BookingStatus::Pending)->exists();
    }

    /**
     * ID gedung yang dikelola (untuk scope BR-08).
     *
     * @return list<int>
     */
    public function managedPropertyIds(): array
    {
        return once(fn () => $this->managedProperties()->pluck('id')->all());
    }

    // Scope

    public function scopeTenants(Builder $query): void
    {
        $query->role(Role::Tenant->value);
    }

    public function scopeManagers(Builder $query): void
    {
        $query->role(Role::Manager->value);
    }

    public function scopeStatus(Builder $query, UserStatus $status): void
    {
        $query->where('status', $status);
    }

    // Atribut

    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->photo_path ? Storage::disk('public')->url($this->photo_path) : null);
    }
}
