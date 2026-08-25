<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Purchase;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class SupplierService
{
    public function createSupplier(array $data, Business $business, User $user): Supplier
    {
        return DB::transaction(function () use ($data, $business, $user) {
            $supplier = Supplier::create([
                'business_id' => $business->id,
                'name' => $data['name'],
                'company_name' => $data['company_name'] ?? null,
                'phone' => $data['phone'] ?? null,
                'email' => $data['email'] ?? null,
                'address' => $data['address'] ?? null,
                'notes' => $data['notes'] ?? null,
                'status' => $data['status'] ?? 'active',
            ]);

            AuditLogger::log(
                action: 'supplier.created',
                entityType: Supplier::class,
                entityId: $supplier->id,
                newValues: ['name' => $supplier->name, 'company' => $supplier->company_name],
                businessId: $business->id,
                userId: $user->id
            );

            return $supplier;
        });
    }

    public function updateSupplier(Supplier $supplier, array $data, User $user): Supplier
    {
        return DB::transaction(function () use ($supplier, $data, $user) {
            $oldValues = $supplier->only(['name', 'company_name', 'phone', 'email', 'address', 'notes', 'status']);
            $supplier->update($data);

            AuditLogger::log(
                action: 'supplier.updated',
                entityType: Supplier::class,
                entityId: $supplier->id,
                oldValues: $oldValues,
                newValues: $supplier->only(array_keys($oldValues)),
                businessId: $supplier->business_id,
                userId: $user->id
            );

            return $supplier;
        });
    }

    public function archiveSupplier(Supplier $supplier, User $user): Supplier
    {
        return DB::transaction(function () use ($supplier, $user) {
            $supplier->delete(); // Soft-delete preserves purchase relations

            AuditLogger::log(
                action: 'supplier.archived',
                entityType: Supplier::class,
                entityId: $supplier->id,
                businessId: $supplier->business_id,
                userId: $user->id
            );

            return $supplier;
        });
    }

    public function getFiltered(Business $business, array $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = Supplier::where('business_id', $business->id)
            ->withCount(['purchases' => function ($q) {
                $q->where('status', Purchase::STATUS_COMPLETED);
            }])
            ->withSum(['purchases' => function ($q) {
                $q->where('status', Purchase::STATUS_COMPLETED);
            }], 'total_amount');

        if (!empty($filters['search'])) {
            $search = '%' . trim($filters['search']) . '%';
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', $search)
                  ->orWhere('company_name', 'like', $search)
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
        $totalSuppliers = Supplier::where('business_id', $business->id)->count();
        $activeSuppliers = Supplier::where('business_id', $business->id)->where('status', 'active')->count();

        return [
            'total_suppliers' => $totalSuppliers,
            'active_suppliers' => $activeSuppliers,
        ];
    }
}
