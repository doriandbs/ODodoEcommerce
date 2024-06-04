<?php

namespace App\Service;
use App\Repository\CartRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CartService
{
    private $cartRepository;
    private $security;

    public function __construct(CartRepository $cartRepository, Security $security)
    {
        $this->cartRepository = $cartRepository;
        $this->security = $security;
    }

    public function getCartItemCount(): int
    {
        $user = $this->security->getUser();
        if (!$user) {
            return 0;
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user, 'save' => false]);
        if (!$cart) {
            return 0;
        }

        return $cart->getProductCarts()->count();
    }
}