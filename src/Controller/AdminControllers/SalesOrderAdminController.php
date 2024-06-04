<?php

namespace App\Controller\AdminControllers;

use App\Entity\SalesOrder;
use App\Form\SalesOrderType;
use App\Repository\SalesOrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/sales/order/admin')]
class SalesOrderAdminController extends AbstractController
{
    #[Route('/', name: 'app_sales_order_admin_index', methods: ['GET'])]
    public function index(SalesOrderRepository $salesOrderRepository): Response
    {
        return $this->render('admin/admin_sales_order/index.html.twig', [
            'sales_orders' => $salesOrderRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_sales_order_admin_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $salesOrder = new SalesOrder();
        $form = $this->createForm(SalesOrderType::class, $salesOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($salesOrder);
            $entityManager->flush();

            return $this->redirectToRoute('app_sales_order_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_sales_order/new.html.twig', [
            'sales_order' => $salesOrder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sales_order_admin_show', methods: ['GET'])]
    public function show(SalesOrder $salesOrder): Response
    {
        return $this->render('admin/admin_sales_order/show.html.twig', [
            'sales_order' => $salesOrder,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_sales_order_admin_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, SalesOrder $salesOrder, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(SalesOrderType::class, $salesOrder);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_sales_order_admin_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('admin/admin_sales_order/edit.html.twig', [
            'sales_order' => $salesOrder,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_sales_order_admin_delete', methods: ['POST'])]
    public function delete(Request $request, SalesOrder $salesOrder, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$salesOrder->getId(), $request->request->get('_token'))) {
            $entityManager->remove($salesOrder);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_sales_order_admin_index', [], Response::HTTP_SEE_OTHER);
    }
}
