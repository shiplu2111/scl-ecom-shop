<?php

namespace App\Http\Controllers\API\V1\Admin;

use App\Http\Controllers\API\V1\BaseController;
use App\Http\Resources\TicketResource;
use App\Http\Resources\TicketMessageResource;
use App\Services\TicketService;
use Illuminate\Http\Request;

/**
 * @group Admin
 * @subgroup Ticket Management
 */
class TicketController extends BaseController
{
    protected TicketService $ticketService;

    public function __construct(TicketService $ticketService)
    {
        $this->ticketService = $ticketService;
    }

    /**
     * List all customer tickets.
     */
    public function index(Request $request)
    {
        $tickets = $this->ticketService->fetchAll($request->all());
        return $this->successResponse(TicketResource::collection($tickets), 'Tickets fetched successfully');
    }

    /**
     * View ticket details.
     */
    public function show(int $id)
    {
        $ticket = $this->ticketService->findWithDetails($id);
        if (!$ticket) return $this->errorResponse('Ticket not found', 404);

        return $this->successResponse(new TicketResource($ticket), 'Ticket details fetched successfully');
    }

    /**
     * Reply to a ticket.
     */
    public function reply(Request $request, int $id)
    {
        $ticket = $this->ticketService->findWithDetails($id);
        if (!$ticket) return $this->errorResponse('Ticket not found', 404);

        $validated = $request->validate([
            'message'     => 'required|string',
            'attachments' => 'sometimes|array',
            'status'      => 'sometimes|in:open,pending,resolved,closed',
        ]);

        $message = $this->ticketService->reply($ticket, $validated, auth('admin')->user());
        
        if (!empty($validated['status'])) {
            $this->ticketService->updateStatus($ticket, $validated['status']);
        }

        return $this->successResponse(new TicketMessageResource($message), 'Reply sent successfully');
    }

    /**
     * Update ticket status or priority.
     */
    public function updateStatus(Request $request, int $id)
    {
        $ticket = $this->ticketService->findWithDetails($id);
        if (!$ticket) return $this->errorResponse('Ticket not found', 404);

        $validated = $request->validate([
            'status'   => 'sometimes|in:open,pending,resolved,closed',
            'priority' => 'sometimes|in:low,medium,high,urgent',
        ]);

        if (isset($validated['status'])) {
            $this->ticketService->updateStatus($ticket, $validated['status']);
        }

        if (isset($validated['priority'])) {
            $this->ticketService->updatePriority($ticket, $validated['priority']);
        }

        return $this->successResponse(new TicketResource($ticket), 'Ticket updated successfully');
    }
}
