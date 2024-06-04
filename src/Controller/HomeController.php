<?php

namespace App\Controller;

use App\Entity\File;
use App\Repository\CartRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\SalesOrderRepository;
use App\Repository\UserRepository;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{

    private $cartRepository;
    private $security;
    protected $cartCount;

    public function __construct(CartRepository $cartRepository, Security $security)
    {
        $this->cartRepository = $cartRepository;
        $this->security = $security;
        $user = $this->security->getUser();
        if (!$user) {
            return 0;
        }

        $cart = $this->cartRepository->findOneBy(['user' => $user, 'save' => false]);
        if (!$cart) {
            return 0;
        }

        $this->cartCount = $cart->getProductCarts()->count();    
    }

    #[Route('/', name: 'app_home')]
    public function index(CartRepository $cartRepository,SalesOrderRepository $salesOrderRepository, UserRepository $userRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository, ImageManager $imageManager, EntityManagerInterface $em): Response
    {
        $targetDirectory = $imageManager->getTargetDirectory();
        $categories = $categoryRepository->findAll();
        $products = $productRepository->findAll();
        $images = $em->getRepository(File::class)->findAll();
        if ($this->isGranted('ROLE_ADMIN')) {
            $totalUsers = $userRepository->count([]);

            $totalOrders = $salesOrderRepository->count([]);
    
            $pendingOrders = $cartRepository->count(['save' => false]);
   
            $revenue = 0; 
    
            return $this->render('admin/index.html.twig', [
                'totalUsers' => $totalUsers,
                'totalOrders' => $totalOrders,
                'pendingOrders' => $pendingOrders,
                'monthlyRevenue' => $revenue,
            ]);        } else {
            return $this->render('home.html.twig', [
                'products' => $products,
                'controller_name' => 'HomeController',
                'categories' => $categories,
                'target_directory' => $targetDirectory, // Assurez-vous que cette variable est correcte
                'images' => $images
            ]);
        }
        
    }

    protected function render(string $view, array $parameters = [], ?Response $response = null) : Response
    {
     $parameters = array_merge($parameters, ['cartCount' => $this->cartCount]);
        return parent::render($view,$parameters,$response);
    }
}
