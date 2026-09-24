<?php

namespace Tests\Feature;

use Tests\TestCase;

class ErrorPageTest extends TestCase
{
    public function test_unknown_page_shows_branded_404(): void
    {
        $this->get('/halaman-yang-tidak-ada')
            ->assertNotFound()
            ->assertSee('Kamar ini tidak ada')
            ->assertSee('Ke beranda');
    }
}
