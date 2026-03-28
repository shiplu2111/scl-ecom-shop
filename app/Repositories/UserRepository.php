<?php

namespace App\Repositories;

use App\Models\User;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function searchAndFilter(array $filters)
    {
        $query = $this->model->newQuery();

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        return $query->latest()->paginate($filters['per_page'] ?? 15);
    }

    public function toggleStatus(int $userId)
    {
        $user = $this->find($userId);
        $user->is_active = !$user->is_active;
        $user->save();
        return $user;
    }

    public function findByEmailOrPhone(string $identity)
    {
        return $this->model->where('email', $identity)
                           ->orWhere('phone', $identity)
                           ->first();
    }

    public function updateDeviceId(int $userId, string $deviceId)
    {
        return $this->model->where('id', $userId)->update(['device_id' => $deviceId]);
    }

    public function clearDeviceId(int $userId)
    {
        return $this->model->where('id', $userId)->update(['device_id' => null]);
    }
}
