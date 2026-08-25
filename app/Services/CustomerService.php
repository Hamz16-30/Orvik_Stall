<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Customer;
use App\Models\Sale;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function createCustomer(array $data, Business $business, User $user): Customer
    {
        return DB::transaction(function () use ($data, $business, $user) {
            $customer = Customer::create([
                'business_id' => $business->id,
                'name' => $data['name'],
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            AuditLogger::log(
                action: 'customer.created',
                entityType: Customer::class,
                entityId: $customer->id,
                newValues: ['name' => $customer->name, 'phone' => $customer->phone],
                businessId: $business->id,
                userId: $user->id
            );

            return $customer;
        });
    }

    public function updateCustomer(Customer $customer, array $data, User $user): Customer
    {
        return DB::transaction(function () use ($customer, $data, $user) {
            $oldValues = $customer->only(['name', 'phone', 'email', 'address', 'notes', 'status']);
            $customer->update($data);

            AuditLogger::log(
                action: 'customer.updated',
                entityType: Customer::class,
                entityId: $customer->id,
                oldValues: $oldValues,
                newValues: $customer->only(array_keys($oldValues)),
                businessId: $customer->business_id,
                userId: $user->id
            );

            return $customer;
        });
    }

    public function archiveCustomer(Customer $customer, User $user): Customer
    {
        return DB::transaction(function () use ($customer, $user) {
            $customer->delete(); // Soft-delete preserves sales relations

            AuditLogger::log(
                action: 'customer.archived',
                entityType: Customer::class,
                entityId: $customer->id,
                businessId: $customer->business_id,
                userId: $user->id
            );

            return $customer;
        });
    }

    public function getFiltered(Business $business, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Customer::where('business_id', $business->id)
            ->withCount(['sales' => function ($q) {
                $q->where('status', Sale::STATUS_COMPLETED);
            }])
            ->withSum(['sales' => function ($q) {
                $q->where('status', Sale::STATUS_COMPLETED);
            }], 'total_amount');

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('phone', 'like', $search)
                  ->orWhere('email', 'like', $search);
            });
        }

        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate($perPage)->withQueryString();
    }

    public function getSummary(Business $business): array
    {
        $totalCustomers = Customer::where('business_id', $business->id)->count();
        $activeCustomers = Customer::where('business_id', $business->id)->where('status', 'active')->count();

        return [
            'total_customers' => $totalCustomers,
            'active_customers' => $activeCustomers,
        ];
    }
}
