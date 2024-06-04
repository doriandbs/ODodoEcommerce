<?php

namespace App\Controller\AdminControllers;

use App\Entity\Category;
use App\Entity\File;
use App\Form\CategoryType;
use App\Repository\CategoryRepository;
use App\Service\ImageManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/category/admin')]
class CategoryAdminController extends AbstractController
{
    #[Route('/', name: 'app_category_admin_index', methods: ['GET'])]
    public function index(CategoryRepository $categoryRepository): Response
    {
        return $this->render('admin/admin_category/index.html.twig', [
            'categories' => $categoryRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_category_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager,ImageManager $imageManager): Response
    {
        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        $file = new File();

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadFile = $form->get('image')->getData();
            $fileName = $imageManager->upload($uploadFile, 1);
            $file->setPath($fileName);
            $file->setType('image');
            $file->setName($uploadFile->getClientOriginalName());
            $file->setCreatedOn(new \DateTimeImmutable());
            $file->setPublic(true);
            
            $category->setImage($file);

            $entityManager->persist($file);
            $entityManager->persist($category);
            $entityManager->flush();

            return $this->redirectToRoute('app_category_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_category/new.html.twig', [
            'category' => $category,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}', name: 'app_category_admin_show', methods: ['GET'])]
    public function show(Category $category): Response
    {
        return $this->render('admin/admin_category/show.html.twig', [
            'category' => $category,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_category_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_category/edit.html.twig', [
            'category' => $category,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_category_admin_delete', methods: ['POST'])]
    public function delete(Request $request, Category $category, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $entityManager->remove($category);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_category_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
