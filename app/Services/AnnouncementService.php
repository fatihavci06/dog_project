<?php
namespace App\Services;

use App\Models\Announcement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class AnnouncementService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        $query = Announcement::with('role');

        if (!empty($filters['role_id'])) {
            $query->where('role_id', $filters['role_id']);
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate(20);
    }
    public function apiGetList(array $filters = []): LengthAwarePaginator
    {

        $query = Announcement::query();


        if (!empty($filters['role_id'])) {
            $roleId = $filters['role_id'];
            $query->where(function ($q) use ($roleId) {
                $q->where('role_id', $roleId)
                    ->orWhereNull('role_id'); // role_id null olanlar da gelsin
            });
        }

        if (!empty($filters['search'])) {
            $query->where('title', 'like', '%' . $filters['search'] . '%');
        }
            $query->select('id', 'title', 'content');

        return $query->latest()->paginate(20);
    }

    public function create(array $data): Announcement
    {
        return Announcement::create($data);
    }

    public function update(Announcement $announcement, array $data): bool
    {
        return $announcement->update($data);
    }

    public function delete(Announcement $announcement)
    {
        return $announcement->delete();
    }
    public function findById(array $data,string $id): ?Announcement
    {
        $query = Announcement::query();
        $roleId = $data['role_id'];
            $query->where(function ($q) use ($roleId) {
                $q->where('role_id', $roleId)
                    ->orWhereNull('role_id'); // role_id null olanlar da gelsin
            });
        return $query->find($id);
    }
}
