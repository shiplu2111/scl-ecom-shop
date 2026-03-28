<?php

namespace Tests\Feature\API\V1;

use App\Models\Admin;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminProductTest extends TestCase
{
    use RefreshDatabase;

    protected $token;
    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->admin = Admin::factory()->create();
        $this->admin->assignRole('super_admin');
        $this->token = JWTAuth::fromUser($this->admin);
    }

    public function test_admin_can_list_products()
    {
        Product::factory()->count(3)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/v1/admin/products');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_product_with_variants()
    {
        $category = Category::factory()->create();
        $brand = Brand::factory()->create();

        $data = [
            'category_id' => $category->id,
            'brand_id'    => $brand->id,
            'name'         => 'New Product',
            'sku'          => 'PROD-100',
            'price'        => 99.99,
            'variants'     => [
                [
                    'sku'   => 'VAR-1',
                    'size'  => 'M',
                    'color' => 'Red',
                    'stock' => 10,
                    'price' => 99.99
                ]
            ]
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->postJson('/api/v1/admin/products', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'New Product');
        
        $this->assertDatabaseHas('products', ['sku' => 'PROD-100']);
        $this->assertDatabaseHas('product_variants', ['sku' => 'VAR-1']);
    }

    public function test_admin_can_update_product()
    {
        $product = Product::factory()->create(['name' => 'Old Product']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->putJson("/api/v1/admin/products/{$product->id}", [
            'name' => 'Updated Product'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'Updated Product');
    }

    public function test_admin_can_delete_product()
    {
        $product = Product::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->deleteJson("/api/v1/admin/products/{$product->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('products', ['id' => $product->id]);
    }
}
