<?php

namespace App\Controller\AdminControllers;

use App\Entity\File;
use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/product')]
class ProductAdminController extends AbstractController
{
    #[Route('/', name: 'app_product_admin_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('admin/admin_product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_product_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, ImageManager $imageManager): Response
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);

        $file = new File();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $formData = $form->getData();
            $uploadFile = $form->get('image')->getData();
            $public = $formData->getPublic(); 

            $fileName = $imageManager->upload($uploadFile, 1);
            $file->setPath($fileName);
            $file->setType('image');
            $file->setCreatedOn(new \DateTimeImmutable());
            $file->setPublic($public);
            //$file->setPublic(true);  //Pour supprimer dans ProductType le champs pulic
            $file->setName($uploadFile->getClientOriginalName());
            $file->setCreatedOn(new \DateTimeImmutable());

            $product->setImage($file);

            $entityManager->persist($file);
            $entityManager->persist($product);
            $entityManager->flush();

            return $this->redirectToRoute('app_product_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_product/new.html.twig', [
            'product' => $product,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_product_admin_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('admin/admin_product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_product_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_product_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_product/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_product_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Product $product, EntityManagerInterface $entityManager): Response
    {
    
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token'))) {
            $product->setDeletedDate(new \DateTimeImmutable('now'));
            $entityManager->flush();
        }


        return $this->redirectToRoute('app_product_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
