<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $properties = Property::active()->orderBy('updated_at', 'desc')->get(['slug', 'updated_at']);

        $urls = collect([
            ['loc' => route('home'), 'lastmod' => now(), 'priority' => '1.0'],
            ['loc' => route('kost.index'), 'lastmod' => now(), 'priority' => '0.9'],
            ['loc' => route('kost.submissions.create'), 'lastmod' => now(), 'priority' => '0.5'],
        ])->concat($properties->map(fn (Property $property) => [
            'loc' => route('kost.show', $property),
            'lastmod' => $property->updated_at,
            'priority' => '0.8',
        ]));

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
