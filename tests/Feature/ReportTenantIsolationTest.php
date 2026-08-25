<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\BusinessUser;
use App\Models\Role;
use App\Models\Sale;
use App\Models\User;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReportTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    protected Business $businessA;
    protected Business $businessB;
    protected User $ownerA;
    protected User $ownerB;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $ownerRole = Role::where('name', Role::OWNER)->first();

        // Business A
        $this->ownerA = User::create([
            'name' => 'Owner Stall A',
            'email' => 'owner_a@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $this->businessA = Business::create([
            'name' => 'Stall A',
            'owner_id' => $this->ownerA->id,
            'currency' => 'PKR',
        ]);
        BusinessUser::create([
            'business_id' => $this->businessA->id,
            'user_id' => $this->ownerA->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // Business B
        $this->ownerB = User::create([
            'name' => 'Owner Stall B',
            'email' => 'owner_b@orvikstore.com',
            'password' => Hash::make('password123'),
            'status' => 'active',
        ]);
        $this->businessB = Business::create([
            'name' => 'Stall B',
            'owner_id' => $this->ownerB->id,
            'currency' => 'USD',
        ]);
        BusinessUser::create([
            'business_id' => $this->businessB->id,
            'user_id' => $this->ownerB->id,
            'role_id' => $ownerRole->id,
            'status' => 'active',
            'joined_at' => now(),
        ]);
    }

    public function test_tenant_isolation_in_reports_and_exports(): void
    {
        // Business A has 10,000 in sales
        Sale::create([
            'business_id' => $this->businessA->id,
            'invoice_number' => 'INV-STALL-A',
            'subtotal' => 10000.00,
            'total_amount' => 10000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // Business B has 99,000 in sales
        Sale::create([
            'business_id' => $this->businessB->id,
            'invoice_number' => 'INV-STALL-B',
            'subtotal' => 99000.00,
            'total_amount' => 99000.00,
            'payment_method' => 'cash',
            'status' => Sale::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->actingAs($this->ownerA);

        $response = $this->get('/app/reports/sales');
        $response->assertOk();
        $response->assertSee('INV-STALL-A');
        $response->assertSee('10,000.00');
        $response->assertDontSee('INV-STALL-B');
        $response->assertDontSee('99,000.00');
    }
}
