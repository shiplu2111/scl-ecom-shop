<?php

namespace App\Repositories;

use App\Models\Ticket;

class TicketRepository extends BaseRepository implements TicketRepositoryInterface
{
    public function __construct(Ticket $model)
    {
        parent::__construct($model);
    }

    public function searchAndFilter(array $filters)
    {
        $query = $this->model->with(['user', 'latestMessage']);

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        if (!empty($filters['ticket_number'])) {
            $query->where('ticket_number', 'like', "%{$filters['ticket_number']}%");
        }

        return $query->latest('last_reply_at')->paginate($filters['per_page'] ?? 15);
    }

    public function findByTicketNumber(string $number)
    {
        return $this->model->where('ticket_number', $number)->first();
    }

    public function getUserTickets(int $userId, array $filters)
    {
        return $this->model->where('user_id', $userId)
            ->latest()
            ->paginate($filters['per_page'] ?? 15);
    }
}
