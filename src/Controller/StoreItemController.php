<?php

namespace App\Controller;

use App\Entity\StoreItem;
use App\Form\StoreItemType;
use App\Repository\StoreItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/store/item')]
class StoreItemController extends AbstractController
{
    #[Route('/', name: 'app_store_item_index', methods: ['GET'])]
    public function index(StoreItemRepository $storeItemRepository): Response
    {
        return $this->render('store_item/index.html.twig', [
            'store_items' => $storeItemRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_store_item_new', methods: ['GET', 'POST'])]
    public function new(Request $request, StoreItemRepository $storeItemRepository): Response
    {
        $storeItem = new StoreItem();
        $form = $this->createForm(StoreItemType::class, $storeItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storeItemRepository->save($storeItem, true);

            return $this->redirectToRoute('app_store_item_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('store_item/new.html.twig', [
            'store_item' => $storeItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_store_item_show', methods: ['GET'])]
    public function show(StoreItem $storeItem): Response
    {
        return $this->render('store_item/show.html.twig', [
            'store_item' => $storeItem,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_store_item_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, StoreItem $storeItem, StoreItemRepository $storeItemRepository): Response
    {
        $form = $this->createForm(StoreItemType::class, $storeItem);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $storeItemRepository->save($storeItem, true);

            return $this->redirectToRoute('app_store', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('store_item/edit.html.twig', [
            'store_item' => $storeItem,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_store_item_delete', methods: ['POST'])]
    public function delete(Request $request, StoreItem $storeItem, StoreItemRepository $storeItemRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$storeItem->getId(), $request->request->get('_token'))) {
            $storeItemRepository->remove($storeItem, true);
        }

        return $this->redirectToRoute('app_store', [], Response::HTTP_SEE_OTHER);
    }
}
