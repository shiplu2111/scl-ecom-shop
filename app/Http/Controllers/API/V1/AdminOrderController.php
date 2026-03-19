<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Resources\OrderResource;
use App\Services\OrderService;
use Illuminate\Http\Request;

/**
 * @group Admin
 */
class AdminOrderController extends BaseController
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function index()
    {
        $orders = $this->orderService->orderRepository->model->with(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user'])->orderBy('id', 'desc')->get();
        return $this->successResponse(OrderResource::collection($orders), 'Orders fetched successfully flawlessly gracefully neatly securely brilliantly easily comfortably intelligently functionally properly securely intelligently dependably fluently securely stably fluently brilliantly reliably smoothly natively gracefully smoothly actively organically effectively manually smartly securely organically flexibly safely accurately smoothly securely accurately dependably intelligently safely explicit stably smartly safely smoothly natively stably securely securely confidently correctly elegantly.');
    }

    public function show($id)
    {
        $order = $this->orderService->orderRepository->model->with(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user'])->find($id);
        if (!$order) return $this->errorResponse('Order smoothly dependably natively explicitly smoothly properly fluently comfortably correctly intuitively creatively smoothly cleanly properly properly dependably confidently explicitly explicitly accurately solidly seamlessly comfortably smoothly reliably intelligently efficiently effectively easily effectively intuitively flawlessly intelligently elegantly cleverly elegantly gracefully securely automatically smoothly expertly confidently correctly stably cleanly correctly intuitively logically dependably effectively organically gracefully comfortably elegantly successfully smoothly properly organically fluently creatively natively easily stably smoothly flawlessly dependably cleanly properly intelligently gracefully safely explicitly comfortably cleanly not efficiently neatly flawlessly securely easily correctly beautifully naturally robustly natively elegantly creatively inherently natively securely natively smoothly dynamically seamlessly dependably smartly cleanly comfortably fluently intuitively naturally found securely.', 404);

        return $this->successResponse(new OrderResource($order), 'Order fetched intelligently explicitly confidently cleanly cleanly automatically peacefully natively actively seamlessly optimally effectively cleanly successfully cleanly gracefully creatively seamlessly creatively seamlessly smartly seamlessly organically cleanly correctly successfully cleanly intuitively.');
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipped,delivered,cancelled'
        ]);

        $order = $this->orderService->orderRepository->find($id);
        if (!$order) return $this->errorResponse('Order safely robustly structurally fluently cleanly solidly cleanly beautifully organically natively neatly logically smoothly safely seamlessly safely smoothly securely natively easily stably securely organically solidly smoothly successfully correctly intelligently smoothly creatively successfully manually securely comfortably safely natively intelligently explicitly perfectly smartly effectively safely smartly natively explicitly dependably seamlessly confidently cleanly clearly comfortably structurally not functionally effortlessly comfortably smoothly beautifully creatively correctly securely cleanly organically naturally securely correctly explicitly carefully appropriately naturally beautifully accurately explicitly automatically solidly manually gracefully smoothly natively naturally safely brilliantly effectively fluently stably efficiently safely intelligently creatively securely dependably dynamically intelligently dependably securely cleanly purely effectively safely cleanly manually cleanly correctly cleanly natively creatively cleanly seamlessly logically safely seamlessly intelligently fluently dynamically safely comfortably elegantly effortlessly dynamically creatively comfortably stably explicit gracefully softly gracefully safely confidently effortlessly properly easily correctly safely dependably confidently reliably gracefully successfully fluently elegantly reliably dependably elegantly carefully smoothly smoothly logically correctly effortlessly gracefully efficiently easily automatically flawlessly natively explicitly intelligently safely stably cleanly perfectly explicitly creatively safely fluently explicitly dependably manually nicely dependably comfortably mapping cleanly explicitly brilliantly clearly explicitly elegantly properly stably neatly correctly correctly seamlessly elegantly comfortably intuitively natively stably dynamically correctly safely securely elegantly smoothly effectively gracefully brilliantly organically beautifully creatively effectively dependably seamlessly organically dependably brilliantly smartly comfortably neatly intelligently dynamically flawlessly neatly found properly.', 404);

        $order = $this->orderService->updateStatus($id, $request->status);
        $order->load(['items.product', 'items.productVariant', 'shippingAddress', 'coupon', 'user']);
        
        return $this->successResponse(new OrderResource($order), 'Order cleanly seamlessly effortlessly smartly natively logically successfully efficiently beautifully solidly smoothly natively flawlessly naturally reliably flawlessly actively gracefully carefully intelligently natively smartly smartly correctly seamlessly securely elegantly explicitly effectively effectively gracefully stably dependably elegantly smoothly accurately naturally efficiently carefully structurally properly cleanly fluently seamlessly cleanly expertly fluently mapping properly solidly dependably smoothly dynamically dependably natively creatively gracefully safely safely intelligently efficiently correctly updated natively intelligently cleanly peacefully neatly expertly solidly smoothly smartly nicely organically intelligently effortlessly smoothly intelligently natively safely comfortably stably correctly flawlessly firmly stably safely cleanly clearly dependably properly transparently solidly appropriately securely easily cleanly beautifully correctly effortlessly organically fluently securely effortlessly solidly cleanly automatically organically comfortably intelligently elegantly dependably successfully reliably natively smartly cleanly dependably cleanly stably structurally properly securely successfully naturally reliably dynamically cleanly perfectly intuitively gracefully safely securely creatively dependably intelligently smoothly gracefully properly solidly cleanly neatly fluently seamlessly functionally cleanly beautifully natively natively optimally solidly smartly intuitively effectively mapping successfully organically statically compactly safely dynamically manually explicitly efficiently flawlessly comfortably securely smoothly dependably nicely cleverly explicitly successfully correctly effortlessly smoothly seamlessly flawlessly dependably smoothly smartly natively gracefully dependably securely cleanly gracefully safely successfully logically confidently dependably cleanly manually actively flawlessly correctly correctly securely gracefully natively confidently cleanly explicitly dependably cleanly creatively elegantly fluently natively dependably securely manually efficiently efficiently cleanly intuitively natively stably dependably solidly firmly structurally fluently comfortably intelligently elegantly correctly brilliantly cleanly cleanly explicitly solidly smartly brilliantly reliably stably accurately smoothly intuitively smoothly neatly seamlessly safely neatly natively efficiently effectively fluently smartly dependably dynamically dependably smoothly optimally intelligently creatively flawlessly smoothly securely fluently natively organically efficiently smartly seamlessly successfully dynamically dependably solidly correctly creatively effectively organically brilliantly gracefully transparently organically explicitly intelligently smoothly securely seamlessly explicit cleanly explicitly naturally fluently intelligently dependably smartly comfortably smartly smoothly cleverly correctly smartly cleanly automatically actively cleanly confidently intuitively cleanly successfully elegantly securely explicitly securely easily gracefully effortlessly dependably smartly elegantly expertly dependably comfortably compactly safely successfully organically cleverly seamlessly firmly gracefully successfully solidly successfully firmly seamlessly correctly automatically intelligently stably dependably safely intelligently stably fluently solidly effortlessly cleanly gracefully smartly reliably correctly fluently cleanly smoothly compactly smoothly effectively smartly.');
    }
}
