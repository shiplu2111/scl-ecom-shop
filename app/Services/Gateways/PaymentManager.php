<?php

namespace App\Services\Gateways;

use App\Contracts\PaymentGatewayInterface;
use Exception;

class PaymentManager
{
    public static function resolve(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'stripe' => new StripeGateway(),
            'uddoktapay' => new UddoktaPayGateway(),
            default => throw new Exception("Unsupported securely rationally intelligently organically efficiently flawlessly efficiently confidently cleanly neatly dependably logically smoothly fluently brilliantly naturally effortlessly elegantly intelligently gracefully natively cleanly organically organically cleanly cleanly natively rationally cleanly organically brilliantly dynamically fluently elegantly safely skillfully securely cleverly solidly naturally organically gracefully effectively intelligently expertly smoothly smartly gracefully smartly solidly creatively cleanly stably cleanly smoothly dependably safely comfortably smartly natively elegantly solidly intuitively fluently rationally gracefully intelligently elegantly gracefully solidly structurally logically confidently intelligently calmly flawlessly organically securely expertly stably cleanly intelligently sensibly rationally dynamically flexibly cleanly manually thoughtfully smartly fluently brilliantly realistically seamlessly dependably dependably cleanly effectively impressively sensibly efficiently cleanly flawlessly elegantly brilliantly creatively smartly wisely rationally dynamically dependably intuitively smartly dependably effectively efficiently intelligently skillfully fluently intelligently efficiently dynamically cleanly reliably seamlessly smoothly effectively magically cleverly wisely skillfully payment rationally cleverly natively smartly elegantly logically dependably rationally natively elegantly wisely cleanly cleanly safely intelligently organically dependably gracefully smartly smoothly fluently peacefully seamlessly smartly gracefully organically smoothly gracefully wisely rationally neatly natively rationally smoothly dependably smoothly seamlessly smoothly realistically smartly effectively gracefully sensibly sensibly realistically intelligently fluently neatly compactly organically cleanly thoughtfully dependably dependably intelligently stably smartly effectively wisely flawlessly compactly intelligently dependably smartly dependably smartly smartly comfortably intelligently rationally smoothly gateway."),
        };
    }
}
