<?php

namespace App\Controller\AdminControllers;

use App\Entity\Cart;
use App\Entity\Product;
use App\Entity\ProductCart;
use App\Repository\CartRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
#[Route('/xhr/', name:('app_xhr_'))]
class XhrController extends AdminController
{
    #[Route('cart/add/{id}', name : 'cart_add')]
    public function cartAdd(string $id, EntityManagerInterface $em, Request $request, CartRepository $cartRepository, Security $security)
    {
        $user = $this->getUser(); 
        $product = $em->getRepository(Product::class)->find($id);
        if($product==null){
            return new Response('Product not found', 404);
        }
        if(!$user){
            $session = $request->getSession();
            $cart = $session->get('cart', []);
       if (isset($cart[$id])) {
            $cart[$id]['quantity']++;
        } else {
            $cart[$id] = [
                'quantity' => 1,
                'price' => $product->getPriceHT(),
                'name' => $product->getName()
            ];
        }
            $session->set('cart', $cart);
            return new Response(count($cart));
        }
        else{

            $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
            if (!$cart) {
                $cart = new Cart();
                $cart->setUser($user);
                $cart->setTotal(0);
                $cart->setSave(0);
                $em->persist($cart);
            }
    
        $productCart = $em->getRepository(ProductCart::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($productCart) {
            $productCart->setQuantity($productCart->getQuantity() + 1);
            
        } else {
            $productCart = new ProductCart();
            $productCart->setCart($cart);
            $productCart->setProduct($product);
            $productCart->setQuantity(1);
            $em->persist($productCart);
        }

        $currentTotal = $cart->getTotal();
        $cart->setTotal($currentTotal + $product->getPriceHT() * $productCart->getQuantity());
        
    
        $em->flush();
        $em->clear();

        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user]);
        $productCart = $cart->getProductCarts();

        return new Response($productCart->count());
          
    
    }
    }
}