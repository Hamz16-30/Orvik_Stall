<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Role;
use App\Models\Supplier;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
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
            'name' => 'Main Stall 01',
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

    public function test_supplier_crud_and_archiving(): void
    {
        $this->actingAs($this->owner);

        // 1. Create
        $response = $this->post('/app/suppliers', [
            'name' => 'Tariq Wholesale',
            'company_name' => 'Tariq & Sons Distribution',
            'phone' => '03211234567',
            'email' => 'tariq@distribution.com',
            'address' => 'Warehouse 12, Grain Market',
        ]);

        $supplier = Supplier::where('name', 'Tariq Wholesale')->first();
        $this->assertNotNull($supplier);
        $response->assertRedirect('/app/suppliers/' . $supplier->id);

        // 2. Update
        $updateResponse = $this->put('/app/suppliers/' . $supplier->id, [
            'name' => 'Tariq Mega Distribution',
            'company_name' => 'Tariq & Sons Mega Distribution',
            'phone' => '03219998877',
        ]);
        $updateResponse->assertRedirect('/app/suppliers/' . $supplier->id);

        $supplier->refresh();
        $this->assertEquals('Tariq Mega Distribution', $supplier->name);
        $this->assertEquals('03219998877', $supplier->phone);

        // 3. Archive
        $deleteResponse = $this->delete('/app/suppliers/' . $supplier->id);
        $deleteResponse->assertRedirect('/app/suppliers');

        $this->assertSoftDeleted('suppliers', ['id' => $supplier->id]);
    }
}
