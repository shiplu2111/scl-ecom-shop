<?php

namespace Tests\Feature\API\V1;

use App\Models\Admin;
use App\Models\Brand;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminBrandTest extends TestCase
{
    use RefreshDatabase;

    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(PermissionSeeder::class);
        $this->admin = Admin::factory()->create();
        $this->admin->assignRole('super_admin');
        $this->token = JWTAuth::fromUser($this->admin);
    }

    public function test_admin_can_list_brands()
    {
        Brand::factory()->count(3)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/v1/admin/brands');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_brand()
    {
        $data = [
            'name' => 'Test Brand',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->postJson('/api/v1/admin/brands', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Test Brand');
        
        $this->assertDatabaseHas('brands', ['name' => 'Test Brand']);
    }

    public function test_admin_can_update_brand()
    {
        $brand = Brand::factory()->create(['name' => 'Old Brand']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->putJson("/api/v1/admin/brands/{$brand->id}", [
            'name' => 'New Brand'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'New Brand');
    }

    public function test_admin_can_delete_brand()
    {
        $brand = Brand::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->deleteJson("/api/v1/admin/brands/{$brand->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('brands', ['id' => $brand->id]);
    }
}
