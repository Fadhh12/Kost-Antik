<?php

namespace Tests\Feature;

use App\Models\Property;
use App\Models\Room;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_sitemap_lists_active_properties_only(): void
    {
        Room::factory()->for(Property::factory()->state(['name' => 'Kost Tampil', 'slug' => 'kost-tampil']))->create();
        Room::factory()->for(Property::factory()->inactive()->state(['name' => 'Kost Sembunyi', 'slug' => 'kost-sembunyi']))->create();

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml')
            ->assertSee('kost-tampil')
            ->assertDontSee('kost-sembunyi');
    }

    public function test_robots_txt_points_to_sitemap_and_blocks_admin(): void
    {
        $contents = file_get_contents(public_path('robots.txt'));

        $this->assertStringContainsString('Sitemap: /sitemap.xml', $contents);
        $this->assertStringContainsString('Disallow: /admin', $contents);
    }

    public function test_home_page_has_og_image(): void
    {
        $this->get('/')->assertOk()->assertSee('og:image', false);
    }
}
