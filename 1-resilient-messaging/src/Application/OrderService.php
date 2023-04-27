<?php

declare(strict_types=1);

namespace App\Application;

use App\Domain\Order;
use App\Domain\OrderRepository;
use App\Domain\OrderWasPlaced;
use App\Domain\ShippingService;
use Ecotone\Messaging\Attribute\Asynchronous;
use Ecotone\Modelling\Attribute\CommandHandler;
use Ecotone\Modelling\Attribute\EventHandler;
use Ecotone\Modelling\EventBus;

final class OrderService
{
    #[CommandHandler]
    public function placeOrder(PlaceOrder $placeOrder, OrderRepository $orderRepository, EventBus $eventBus): void
    {
        $order = Order::create($placeOrder->orderId, $placeOrder->productName);
        $orderRepository->save($order);

        $eventBus->publish(new OrderWasPlaced($placeOrder->orderId));
    }

    #[Asynchronous("orders")]
    #[EventHandler(endpointId: "ship_order")]
    public function when(OrderWasPlaced $event, OrderRepository $orderRepository, ShippingService $shippingService): void
    {
        $order = $orderRepository->get($event->orderId);

        $shippingService->ship($order);
    }
}