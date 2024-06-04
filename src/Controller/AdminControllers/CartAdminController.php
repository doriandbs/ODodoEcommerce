<?php

namespace App\Controller\AdminControllers;

use App\Entity\Cart;
use App\Entity\ProductCart;
use App\Form\CartType;
use App\Repository\CartRepository;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/cart/admin')]
class CartAdminController extends AbstractController
{
    #[Route('/', name: 'app_cart_admin_index', methods: ['GET'])]
    public function index(CartRepository $cartRepository, ProductRepository $productRepository): Response
    {
        return $this->render('admin/admin_cart/index.html.twig', [
            'carts' => $cartRepository->findAll(),
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_cart_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $cart = new Cart();
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($cart);
            $entityManager->flush();

            return $this->redirectToRoute('app_cart_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_cart/new.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cart_admin_show', methods: ['GET'])]
    public function show(Cart $cart): Response
    {
        return $this->render('admin/admin_cart/show.html.twig', [
            'cart' => $cart,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_cart_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CartType::class, $cart);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_cart_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_cart/edit.html.twig', [
            'cart' => $cart,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_cart_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Cart $cart, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$cart->getId(), $request->request->get('_token'))) {
            $entityManager->remove($cart);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_cart_admin_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/add-product', name: 'app_cart_admin_add_product', methods: ['POST'])]
    public function addProduct(Request $request, Cart $cart, ProductRepository $productRepository, EntityManagerInterface $entityManager): Response
    {
        $productId = $request->request->get('product_id');
        $quantity = $request->request->get('quantity', 1);
    
        $product = $productRepository->find($productId);
        if ($product) {
            $productCart = new ProductCart();
            $productCart->setProduct($product);
            $productCart->setCart($cart);
            $productCart->setQuantity($quantity);
    
            $cart->addProductCart($productCart);
    
            $cart->recalculateTotal();
    
            $entityManager->persist($cart);
            $entityManager->flush();
        }
    
        return $this->redirectToRoute('app_cart_admin_index');
    }
    
    #[Route('/{cartId}/edit-product/{productId}', name: 'app_cart_admin_edit_product', methods: ['POST'])]
public function editProduct(Request $request, int $cartId, int $productId, EntityManagerInterface $entityManager): Response
{
    $newQuantity = $request->request->get('quantity');

    $cart = $entityManager->getRepository(Cart::class)->find($cartId);
    if ($cart) {
        $productCart = $entityManager->getRepository(ProductCart::class)->findOneBy([
            'cart' => $cartId,
            'product' => $productId
        ]);
        if ($productCart) {
            $productCart->setQuantity($newQuantity);

            $cart->recalculateTotal();

            $entityManager->flush();
        }
    }

    return $this->redirectToRoute('app_cart_admin_index');
}

    #[Route('/{cartId}/remove-product/{productId}', name: 'app_cart_admin_remove_product', methods: ['POST'])]
    public function removeProduct(int $cartId, int $productId, EntityManagerInterface $entityManager): Response
    {
        $cart = $entityManager->getRepository(Cart::class)->find($cartId);
        if ($cart) {
            $productCart = $entityManager->getRepository(ProductCart::class)->findOneBy([
                'cart' => $cartId,
                'product' => $productId
            ]);
            if ($productCart) {
                $cart->removeProductCart($productCart);

                $cart->recalculateTotal();

                $entityManager->flush();
            }
        }

        return $this->redirectToRoute('app_cart_admin_index');
    }   

    


}
