<?php

namespace App\Repositories;

interface TicketRepositoryInterface extends BaseRepositoryInterface
{
    public function searchAndFilter(array $filters);
    public function findByTicketNumber(string $number);
    public function getUserTickets(int $userId, array $filters);
}
