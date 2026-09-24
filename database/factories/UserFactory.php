<?php

namespace Database\Factories;

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        $gender = fake()->randomElement(Gender::cases());

        return [
            'name' => fake('id_ID')->name($gender === Gender::Male ? 'male' : 'female'),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '08'.fake()->unique()->numerify('##########'),
            'gender' => $gender,
            'status' => UserStatus::Accepted,
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn () => ['email_verified_at' => null]);
    }

    public function male(): static
    {
        return $this->state(fn () => ['gender' => Gender::Male, 'name' => fake('id_ID')->name('male')]);
    }

    public function female(): static
    {
        return $this->state(fn () => ['gender' => Gender::Female, 'name' => fake('id_ID')->name('female')]);
    }

    public function pending(): static
    {
        return $this->state(fn () => ['status' => UserStatus::Pending]);
    }

    public function rejected(string $reason = 'Data identitas tidak sesuai.'): static
    {
        return $this->state(fn () => ['status' => UserStatus::Rejected, 'status_reason' => $reason]);
    }

    public function inactive(string $reason = 'Sudah tidak tinggal di kost.'): static
    {
        return $this->state(fn () => ['status' => UserStatus::Inactive, 'status_reason' => $reason]);
    }

    public function owner(): static
    {
        return $this->withRole(Role::Owner);
    }

    public function manager(): static
    {
        return $this->withRole(Role::Manager);
    }

    public function tenant(): static
    {
        return $this->withRole(Role::Tenant);
    }

    private function withRole(Role $role): static
    {
        return $this->afterCreating(function (User $user) use ($role) {
            \Spatie\Permission\Models\Role::findOrCreate($role->value, 'web');
            $user->assignRole($role->value);
        });
    }
}
