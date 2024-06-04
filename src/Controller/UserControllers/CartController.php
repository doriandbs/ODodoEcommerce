<?php

namespace App\Controller\UserControllers;

use App\Entity\Cart;
use App\Entity\ProductCart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();
        $cart = [];
        $total = 0;
    
        if ($user) {
            $cartEntity = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
            if ($cartEntity) {
                foreach ($cartEntity->getProductCarts() as $productCart) {
                    $productId = $productCart->getProduct()->getId();
                    $cart[$productId] = [
                        'name' => $productCart->getProduct()->getName(),
                        'price' => $productCart->getProduct()->getPriceHT(),
                        'quantity' => $productCart->getQuantity()
                    ];
                    $total += $productCart->getProduct()->getPriceHT() * $productCart->getQuantity();
                }
            }
        } else {
            $session = $request->getSession();
            $cart = $session->get('cart', []);
            foreach ($cart as $id => $item) {
                $total += $item['price'] * $item['quantity'];
            }
        }
//OUPS
        return $this->render('user/cart/index.html.twig', [
            'cart' => $cart,
            'total' => $total,
        ]);
    }

    #[Route('/remove-from-cart/{id}', name: 'remove_from_cart')]
public function removeFromCart(int $id, Request $request, EntityManagerInterface $entityManager): Response
{
    $user = $this->getUser();
    $session = $request->getSession();

    if (!$user) {
        $cart = $session->get('cart', []);
        if (isset($cart[$id])) {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
    } else {
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        $productCart = $entityManager->getRepository(ProductCart::class)->findOneBy(['cart' => $cart, 'product' => $id]);
        if ($productCart) {
            $entityManager->remove($productCart);
            $entityManager->flush();
        }
    }

    $this->addFlash('success', 'Produit supprimé du panier');
    return $this->redirectToRoute('app_cart');
}


#[Route('/update-cart/{id}', name: 'update_cart')]
public function updateCart(int $id, Request $request, EntityManagerInterface $entityManager): Response
{
    $quantity = $request->request->get('quantity', 0);
    $user = $this->getUser();
    $session = $request->getSession();

    if (!$user) {
        $cart = $session->get('cart', []);
        if (isset($cart[$id]) && $quantity > 0) {
            $cart[$id]['quantity'] = $quantity;
        } elseif ($quantity == 0) {
            unset($cart[$id]); 
        }
        $session->set('cart', $cart);
    } else {
        $cart = $entityManager->getRepository(Cart::class)->findOneBy(['user' => $user]);
        $productCart = $entityManager->getRepository(ProductCart::class)->findOneBy(['cart' => $cart, 'product' => $id]);
        if ($productCart) {
            if ($quantity > 0) {
                $productCart->setQuantity($quantity);
            } else {
                $entityManager->remove($productCart);
            }
            $entityManager->flush();
        }
        if($cart){
            
        }
    }

    $this->addFlash('success', 'Quantité mise à jour');
    return $this->redirectToRoute('app_cart');
}

    

}
