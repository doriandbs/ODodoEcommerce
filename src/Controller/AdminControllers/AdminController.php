<?php

namespace App\Controller\AdminControllers;

use App\Entity\File;
use App\Form\FileType;
use App\Repository\CartRepository;
use App\Repository\SalesOrderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\UserRepository;
use App\Service\ImageManager;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

#[Route('/admin')]
class AdminController extends AbstractController
{
    #[Route('/', name: 'app_addmin')]
    public function index(UserRepository $userRepository, SalesOrderRepository $salesOrderRepository, CartRepository $cartRepository): Response
    {
         $totalUsers = $userRepository->count([]);

         $totalOrders = $salesOrderRepository->count([]);
 
         $pendingOrders = $cartRepository->count(['save' => false]);

         $revenue = 0; 
 
         return $this->render('admin/index.html.twig', [
             'totalUsers' => $totalUsers,
             'totalOrders' => $totalOrders,
             'pendingOrders' => $pendingOrders,
             'monthlyRevenue' => $revenue,
         ]);
    }

    #[Route('/upload', name: 'app_admin_upload')]
    public function upload(Request $request, ImageManager $imageManager, EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $file = new File();

        $form = $this -> createForm(FileType::class, $file);
        $form -> handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $uploadFile = $form->get('image')->getData();
            $fileName = $imageManager->upload($uploadFile, $file->isPublic());

            $file->setPath($fileName);
            $file->setType('image');
            $file->setCreatedOn  (new \DateTimeImmutable());

            $em->persist($file);
            $em->flush();
        }

        return $this->render('admin/admin_images/upload.html.twig', [
            'form' => $form->createView(),
        ]);
    }



    #[Route('/download', name: 'app_admin_download')]
    public function download(EntityManagerInterface $em): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $images = $em->getRepository(File::class)->findAll();

        return $this->render('admin/admin_images/download.html.twig', [
            'images' => $images
        ]);

    }

    #[Route('/image/stream/{id}', name: 'app_image_stream')]
    public function imageStream(int $id, ImageManager $imageManager, EntityManagerInterface $em):Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $image = $em->getRepository(File::class)->find($id);
        $path = $image->getPath();

        return $imageManager->stream($path);
    }

    
}
