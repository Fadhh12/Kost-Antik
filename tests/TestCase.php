<?php

namespace Tests;

use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Role Spatie selalu tersedia untuk test yang memakai RefreshDatabase.
     */
    protected bool $seed = true;

    protected string $seeder = RoleSeeder::class;
}
