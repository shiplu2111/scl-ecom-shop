<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\BlogPost;
use App\Models\Page;
use Illuminate\Http\Response;

/**
 * @group Public
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $products = Product::where('status', 'active')->get();
        $categories = Category::all();
        $posts = BlogPost::where('status', 'published')->get();
        $pages = Page::where('status', 'published')->get();

        $urls = [];

        foreach ($products as $product) {
            $urls[] = url('/products/' . $product->slug);
        }
        foreach ($categories as $category) {
            $urls[] = url('/categories/' . $category->slug);
        }
        foreach ($posts as $post) {
            $urls[] = url('/blog/' . $post->slug);
        }
        foreach ($pages as $page) {
            $urls[] = url('/pages/' . $page->slug);
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url><loc>' . htmlspecialchars($url) . '</loc></url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml'
        ]);
    }
}
