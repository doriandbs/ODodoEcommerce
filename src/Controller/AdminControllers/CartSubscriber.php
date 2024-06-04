<?php
namespace App\Controller\AdminControllers;

use App\Service\CartService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Twig\Environment;

class CartSubscriber implements EventSubscriberInterface
{
    private $twig;
    private $cartService;

    public function __construct(Environment $twig, CartService $cartService)
    {
        $this->twig = $twig;
        $this->cartService = $cartService;
    }

    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onControllerEvent',
        ];
    }

    public function onControllerEvent(ControllerEvent $event)
    {
        $cartItemCount = $this->cartService->getCartItemCount();
        $this->twig->addGlobal('cartItemCount', $cartItemCount);
    }
}
