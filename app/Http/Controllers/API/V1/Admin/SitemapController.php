<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\BlogPost;
use App\Models\Page;
use App\Services\SettingsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\URL;

class SitemapController extends Controller
{
    protected $settings;

    public function __construct(SettingsService $settings)
    {
        $this->settings = $settings;
    }

    public function getStatus()
    {
        $settings = $this->settings->getSettingsByGroup('sitemap');
        
        return response()->json([
            'last_generated' => $settings['sitemap_last_generated'] ?? null,
            'total_urls' => $settings['sitemap_total_urls'] ?? 0,
            'base_url' => $settings['sitemap_base_url'] ?? config('app.url'),
            'sitemap_url' => url('sitemap.xml'),
            'exists' => File::exists(public_path('sitemap.xml'))
        ]);
    }

    public function generate(Request $request)
    {
        $request->validate([
            'base_url' => 'required|url'
        ]);

        $baseUrl = rtrim($request->base_url, '/');
        
        // Save base URL to settings
        $this->settings->updateGroupSettings('sitemap', [
            'sitemap_base_url' => $baseUrl
        ]);

        $urls = [];

        // Static Pages
        $urls[] = ['loc' => $baseUrl . '/', 'lastmod' => now()->toAtomString(), 'changefreq' => 'daily', 'priority' => '1.0'];
        $urls[] = ['loc' => $baseUrl . '/shop', 'lastmod' => now()->toAtomString(), 'changefreq' => 'daily', 'priority' => '0.9'];
        $urls[] = ['loc' => $baseUrl . '/blog', 'lastmod' => now()->toAtomString(), 'changefreq' => 'daily', 'priority' => '0.8'];

        // Categories
        Category::all()->each(function ($item) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => $baseUrl . '/category/' . $item->slug,
                'lastmod' => $item->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        });

        // Brands
        Brand::all()->each(function ($item) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => $baseUrl . '/brand/' . $item->slug,
                'lastmod' => $item->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.6'
            ];
        });

        // Products
        Product::where('is_active', true)->get()->each(function ($item) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => $baseUrl . '/product/' . $item->slug,
                'lastmod' => $item->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.8'
            ];
        });

        // Blog Posts
        BlogPost::where('status', 'published')->get()->each(function ($item) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => $baseUrl . '/blog/' . $item->slug,
                'lastmod' => $item->updated_at->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '0.7'
            ];
        });

        // Dynamic Pages
        Page::where('status', 'published')->get()->each(function ($item) use (&$urls, $baseUrl) {
            $urls[] = [
                'loc' => $baseUrl . '/' . $item->slug,
                'lastmod' => $item->updated_at->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.5'
            ];
        });

        // Generate XML
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL;
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL;

        foreach ($urls as $url) {
            $xml .= '  <url>' . PHP_EOL;
            $xml .= '    <loc>' . htmlspecialchars($url['loc']) . '</loc>' . PHP_EOL;
            $xml .= '    <lastmod>' . $url['lastmod'] . '</lastmod>' . PHP_EOL;
            $xml .= '    <changefreq>' . $url['changefreq'] . '</changefreq>' . PHP_EOL;
            $xml .= '    <priority>' . $url['priority'] . '</priority>' . PHP_EOL;
            $xml .= '  </url>' . PHP_EOL;
        }

        $xml .= '</urlset>';

        // Save to public path
        File::put(public_path('sitemap.xml'), $xml);

        // Update last generated info
        $this->settings->updateGroupSettings('sitemap', [
            'sitemap_last_generated' => now()->toDateTimeString(),
            'sitemap_total_urls' => count($urls)
        ]);

        return response()->json([
            'message' => 'Sitemap generated successfully',
            'total_urls' => count($urls),
            'last_generated' => now()->toDateTimeString(),
            'url' => url('sitemap.xml')
        ]);
    }
}
