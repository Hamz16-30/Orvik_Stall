<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Category;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CategoryManagementTest extends TestCase
{
    use RefreshDatabase;

    protected Business $business;
    protected User $owner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $ownerRole = Role::where('name', Role::OWNER)->first();

        $this->owner = User::create([
            'name' => 'Store Owner',
            'email' => 'owner@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);

        $this->business = Business::create([
            'name' => 'Demo Stall 01',
            'owner_id' => $this->owner->id,
            'currency' => 'PKR',
        ]);

        BusinessUser::create([
            'business_id' => $this->business->id,
            'user_id' => $this->owner->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_category_crud_and_product_safety(): void
    {
        $this->actingAs($this->owner);

        // 1. Create Category
        $response = $this->post('/app/inventory/categories', [
            'name' => 'Cold Beverages',
            'description' => 'Chilled soft drinks and juices',
        ]);
        $response->assertRedirect('/app/inventory/categories');

        $category = Category::where('name', 'Cold Beverages')->first();
        $this->assertNotNull($category);

        // 2. Attach Product to Category
        $product = Product::create([
            'business_id' => $this->business->id,
            'category_id' => $category->id,
            'name' => 'Soda Can',
            'selling_price' => 70.00,
            'stock_quantity' => 20.000,
        ]);

        // 3. Update Category
        $updateResponse = $this->put('/app/inventory/categories/' . $category->id, [
            'name' => 'Cold Beverages & Sodas',
            'description' => 'Updated description',
        ]);
        $updateResponse->assertRedirect('/app/inventory/categories');

        $category->refresh();
        $this->assertEquals('Cold Beverages & Sodas', $category->name);

        // 4. Archive Category
        $deleteResponse = $this->delete('/app/inventory/categories/' . $category->id);
        $deleteResponse->assertRedirect('/app/inventory/categories');

        $this->assertSoftDeleted('categories', ['id' => $category->id]);

        // 5. Ensure Product was NOT deleted
        $product->refresh();
        $this->assertFalse($product->trashed());
        $this->assertEquals('Soda Can', $product->name);
    }
}
