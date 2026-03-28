<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketMessageResource;
use App\Services\TicketService;
use Illuminate\Http\Request;

/**
 * @group Support Tickets
 * @subgroup Customer
 */
class TicketController extends BaseController
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * List all my tickets.
     */
    public function index(Request $request)
    {
        $tickets = $this->ticketService->fetchUserTickets(auth()->user(), $request->all());
        return $this->successResponse(TicketResource::collection($tickets), 'Tickets fetched successfully');
    }

    /**
     * Open a new ticket.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject'     => 'required|string|max:255',
            'message'     => 'required|string',
            'priority'    => 'sometimes|in:low,medium,high,urgent',
            'attachments' => 'sometimes|array',
        ]);

        $ticket = $this->ticketService->createTicket($validated, auth()->user());
        return $this->successResponse(new TicketResource($ticket), 'Ticket opened successfully', 201);
    }

    /**
     * View ticket details and history.
     */
    public function show(int $id)
    {
        $ticket = $this->ticketService->findWithDetails($id);
        
        // Security check
        if ($ticket->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        return $this->successResponse(new TicketResource($ticket), 'Ticket details fetched successfully');
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, int $id)
    {
        $ticket = $this->ticketService->findWithDetails($id);
        
        if ($ticket->user_id !== auth()->id()) {
            return $this->errorResponse('Unauthorized', 403);
        }

        if ($ticket->status === 'closed') {
            return $this->errorResponse('Cannot reply to a closed ticket', 422);
        }

        $validated = $request->validate([
            'message'     => 'required|string',
            'attachments' => 'sometimes|array',
        ]);

        $message = $this->ticketService->reply($ticket, $validated, auth()->user());
        return $this->successResponse(new TicketMessageResource($message), 'Reply sent successfully');
    }
}
