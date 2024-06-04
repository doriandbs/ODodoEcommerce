<?php

namespace App\Controller\UserControllers;

use App\Entity\Cart;
use App\Entity\File;
use App\Entity\ProductCart;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

#[Route('/produits')]
class ProduitsController extends AbstractController
{
    #[Route('/', name: 'app_produits')]
    public function index(Request $request, ProductRepository $productRepository, CategoryRepository $categoryRepository, EntityManagerInterface $em): Response{
  
        $queryParams = $request->query->all();

        $selectedCategories = isset($queryParams['categories']) ? (array) $queryParams['categories'] : [];
        
        $priceMin = !empty($queryParams['priceMin']) ? (int)$queryParams['priceMin'] : null;
        $priceMax = !empty($queryParams['priceMax']) ? (int)$queryParams['priceMax'] : null;
    
        $groupedProducts = $productRepository->findProductsGroupedByCategory($selectedCategories, $priceMin, $priceMax); 

        $categories = $categoryRepository->findAll(); 
        $images = $em->getRepository(File::class)->findAll();


        return $this->render('user/products/products.html.twig', [
            'groupedProducts' => $groupedProducts,
            'categories' => $categories,
            'selectedCategories' => $selectedCategories,
            'priceMin' => $priceMin,
            'priceMax' => $priceMax,
            'images' => $images,
        ]);
    }

    
    #[Route('/{id}', name: 'app_item_produits')]
    public function show(int $id, ProductRepository $repository, EntityManagerInterface $em)
    {
        $product = $repository->find($id);
        $images = $em->getRepository(File::class)->findAll();


        if (!$product) {
            throw $this->createNotFoundException('Le produit demandé n\'existe pas');
        }

        return $this->render('user/products/detail.html.twig', [
            'product' => $product,
            'images' => $images,

        ]);
    }




}
