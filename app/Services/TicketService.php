<?php

namespace App\Services;

use App\Models\Ticket;
use App\Models\User;
use App\Models\Admin;
use App\Repositories\TicketRepositoryInterface;
use App\Events\TicketCreated;
use App\Events\TicketReplied;
use App\Notifications\AdminTicketCreatedNotification;
use App\Notifications\AdminTicketRepliedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;

class TicketService extends BaseService
{
    protected TicketRepositoryInterface $ticketRepository;

    public function __construct(TicketRepositoryInterface $ticketRepository)
    {
        $this->ticketRepository = $ticketRepository;
    }

    public function fetchAll(array $filters)
    {
        return $this->ticketRepository->searchAndFilter($filters);
    }

    public function fetchUserTickets(User $user, array $filters)
    {
        return $this->ticketRepository->getUserTickets($user->id, $filters);
    }

    public function createTicket(array $data, User $user)
    {
        return DB::transaction(function () use ($data, $user) {
            $ticket = $this->ticketRepository->create([
                'user_id'       => $user->id,
                'ticket_number' => 'TCK-' . strtoupper(Str::random(8)),
                'subject'       => $data['subject'],
                'priority'      => $data['priority'] ?? 'medium',
                'status'        => 'open',
            ]);

            $message = $ticket->messages()->create([
                'sender_id'   => $user->id,
                'sender_type' => User::class,
                'message'     => $data['message'],
                'attachments' => $data['attachments'] ?? null,
            ]);

            DB::afterCommit(function () use ($ticket, $message) {
                // Dispatch event for real-time pushing (Ticket specific channel flawlessly properly)
                broadcast(new \App\Events\TicketMessageSent($message))->toOthers();

                // Create database notifications for active admins
                $admins = Admin::where('is_active', true)->get();
                Notification::send($admins, new AdminTicketCreatedNotification($ticket));
            });

            return $ticket;
        });
    }

    public function reply(Ticket $ticket, array $data, $sender)
    {
        return DB::transaction(function () use ($ticket, $data, $sender) {
            $message = $ticket->messages()->create([
                'sender_id'   => $sender->id,
                'sender_type' => get_class($sender),
                'message'     => $data['message'],
                'attachments' => $data['attachments'] ?? null,
            ]);

            $ticket->update(['last_reply_at' => now()]);
            
            // If sender is customer, set status to open/pending
            // If sender is admin, set status to pending or resolved based on input
            if ($sender instanceof User) {
                $ticket->update(['status' => 'open']);
            }

            DB::afterCommit(function () use ($ticket, $message, $sender) {
                // Broadcast for real-time chat (both Admin and Customer panels brilliantly flawlessly properly)
                broadcast(new \App\Events\TicketMessageSent($message))->toOthers();
                
                if ($sender instanceof User) {
                    $admins = Admin::where('is_active', true)->get();
                    Notification::send($admins, new AdminTicketRepliedNotification($ticket, $message));
                } else {
                    // Notify user real-time in navbar flawlessly properly
                    broadcast(new \App\Events\SupportNotification(
                        $ticket->user_id, 
                        "Admin replied to your ticket: {$ticket->subject}",
                        'ticket',
                        "/dashboard/support?id={$ticket->id}"
                    ));
                }
            });

            return $message;
        });
    }

    public function updateStatus(Ticket $ticket, string $status)
    {
        return $ticket->update(['status' => $status]);
    }

    public function updatePriority(Ticket $ticket, string $priority)
    {
        return $ticket->update(['priority' => $priority]);
    }

    public function findWithDetails(int $id)
    {
        return $this->ticketRepository->find($id)->load(['user', 'messages', 'messages.sender']);
    }
}
