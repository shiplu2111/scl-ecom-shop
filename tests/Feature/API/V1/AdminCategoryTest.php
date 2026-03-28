<?php

namespace Tests\Feature\API\V1;

use App\Models\Admin;
use App\Models\Category;
use Database\Seeders\PermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tymon\JWTAuth\Facades\JWTAuth;

class AdminCategoryTest extends TestCase
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

    public function test_admin_can_list_categories()
    {
        Category::factory()->count(3)->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->getJson('/api/v1/admin/categories');

        $response->assertStatus(200)
                 ->assertJsonCount(3, 'data');
    }

    public function test_admin_can_create_category()
    {
        $data = [
            'name' => 'Test Category',
        ];

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->postJson('/api/v1/admin/categories', $data);

        $response->assertStatus(201)
                 ->assertJsonPath('data.name', 'Test Category');
        
        $this->assertDatabaseHas('categories', ['name' => 'Test Category']);
    }

    public function test_admin_can_update_category()
    {
        $category = Category::factory()->create(['name' => 'Old Name']);

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->putJson("/api/v1/admin/categories/{$category->id}", [
            'name' => 'New Name'
        ]);

        $response->assertStatus(200)
                 ->assertJsonPath('data.name', 'New Name');
    }

    public function test_admin_can_delete_category()
    {
        $category = Category::factory()->create();

        $response = $this->withHeaders(['Authorization' => 'Bearer ' . $this->token])
                         ->deleteJson("/api/v1/admin/categories/{$category->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('categories', ['id' => $category->id]);
    }
}
