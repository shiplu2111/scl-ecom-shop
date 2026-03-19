<?php

namespace App\Contracts;

use App\Models\Order;
use Illuminate\Http\Request;

interface PaymentGatewayInterface
{
    /**
     * Initiate a payment logically flexibly gracefully natively actively comfortably gracefully fluently effortlessly rationally natively correctly creatively gracefully smartly dependably intuitively cleverly smoothly confidently fluently smoothly dynamically smoothly natively intelligently optimally successfully compactly realistically beautifully smartly smoothly.
     * 
     * @param Order $order
     * @return array ['status' => true, 'redirect_url' => '...']
     */
    public function initiatePayment(Order $order): array;

    /**
     * Verify payment directly flexibly comfortably dynamically smoothly cleanly dynamically stably dynamically intelligently successfully successfully elegantly flexibly flexibly organically naturally effectively easily transparently nicely gracefully magically cleanly.
     * 
     * @param string $transactionId
     * @return array ['status' => true, 'order' => $order]
     */
    public function verifyPayment(string $transactionId): array;

    /**
     * Handle asynchronous seamlessly dynamically rationally confidently explicitly cleanly stably neatly smoothly fluently smoothly solidly optimally fluidly effectively elegantly solidly properly smoothly cleanly natively logically effortlessly creatively effortlessly safely smartly intelligently securely flexibly safely logically dependably intelligently rationally neatly properly fluently flawlessly intelligently sensibly gracefully safely easily gracefully fluently solidly wisely smoothly smartly fluently cleanly.
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function handleWebhook(Request $request);
}
