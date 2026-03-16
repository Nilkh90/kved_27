<?php

namespace App\Http\Controllers;

use App\Models\Kved2010;
use App\Models\Nace2027;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(24), function (): string {
            $sitemap = Sitemap::create()
                ->add(Url::create(route('home')))
                ->add(Url::create(route('catalog')))
                ->add(Url::create(route('info')));

            Kved2010::query()
                ->select('code')
                ->orderBy('code')
                ->chunk(500, function ($rows) use ($sitemap): void {
                    foreach ($rows as $row) {
                        $sitemap->add(Url::create(route('code.show', ['kved', $row->code])));
                    }
                });

            Nace2027::query()
                ->select('code')
                ->orderBy('code')
                ->chunk(500, function ($rows) use ($sitemap): void {
                    foreach ($rows as $row) {
                        $sitemap->add(Url::create(route('code.show', ['nace', $row->code])));
                    }
                });

            return $sitemap->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}

