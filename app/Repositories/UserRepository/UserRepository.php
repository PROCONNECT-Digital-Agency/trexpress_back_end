<?php

namespace App\Repositories\UserRepository;

use App\Models\Notification;
use App\Models\User;
use App\Repositories\CoreRepository;
use App\Repositories\Interfaces\UserRepoInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository extends CoreRepository implements UserRepoInterface
{

    protected function getModelClass()
    {
        return User::class;
    }

    public function userById(int $id)
    {
        return $this->model()->with('wallet', 'invite.shop', 'shop', 'addresses', 'point')->find($id);
    }

    public function userByUUID(string $uuid)
    {
        return $this->model()->with([
            'shop.translation' => fn($q) => $q->actualTranslation($this->setLanguage()),
            'wallet',
            'addresses',
            'point',
        ])->withAvg('reviews', 'rating')->firstWhere('uuid', $uuid);
    }

    public function usersPaginate(string $perPage, array $array = [], $active = null)
    {
        return $this->model()->with(['shop', 'wallet'])
            ->when(isset($array['role']), function ($q) use ($array) {
                $q->whereHas('roles', function ($q) use ($array) {
                    $q->where('name', $array['role']);
                });
            })
            ->when(isset($array['search']), function ($q) use ($array) {
                $q->where(function ($query) use ($array) {
                    $query->where('firstname', 'LIKE', '%' . $array['search'] . '%')
                        ->orWhere('lastname', 'LIKE', '%' . $array['search'] . '%')
                        ->orWhere('email', 'LIKE', '%' . $array['search'] . '%')
                        ->orWhere('phone', 'LIKE', '%' . $array['search'] . '%');
                });
            })
            ->when(isset($active), function ($q) use ($active) {
                $q->where('active', 1);
            })
            ->orderBy($array['column'] ?? 'id', $array['sort'] ?? 'desc')
            ->when(isset($array['walletSort']), function ($q) use ($array) {
                $q->whereHas('wallet', function ($q) use ($array) {
                    $q->orderBy($array['walletSort'], $array['sort'] ?? 'desc');
                });
            })
            ->paginate($perPage);
    }

    public function usersSearch($search, $active = null, $roles = [])
    {
        return $this->model()->query()
            ->where('id', '!=', auth('sanctum')->id())
            ->when(count($roles) > 0, function ($q) use ($roles) {
                $q->whereHas('roles', function ($q) use ($roles) {
                    $q->whereIn('name', $roles);
                });
            })
            ->where(function ($query) use ($search) {
                $query->where('firstname', 'LIKE', '%' . $search . '%')
                    ->orWhere('lastname', 'LIKE', '%' . $search . '%')
                    ->orWhere('email', 'LIKE', '%' . $search . '%')
                    ->orWhere('phone', 'LIKE', '%' . $search . '%');
            })->when(isset($active), function ($q) use ($active) {
                $q->whereActive($active);
            })->latest()->take(10)->get();
    }

    /**
     * @return Collection|Notification[]
     */
    public function usersNotifications(): array|Collection
    {
        return Notification::get();
    }
}
