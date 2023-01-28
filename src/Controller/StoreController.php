<?php

namespace App\Controller;

use App\Entity\StoreItem;
use App\Form\StoreItemType;
use App\Repository\StoreItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StoreController extends AbstractController
{
    #[Route('/admin/store', name: 'app_store', methods: ['GET', 'POST'])]
    public function index(Request $request, StoreItemRepository $storeItemRepository): Response
    {
        $newStoreItem = new StoreItem();
        $form = $this->createForm(StoreItemType::class);
        $newForm = $this->createForm(StoreItemType::class, $newStoreItem);
        $newForm->handleRequest($request);

        if ($newForm->isSubmitted() && $newForm->isValid()) {
            $storeItemRepository->save($newStoreItem, true);

            return $this->redirectToRoute('app_store', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('store/index.html.twig', [
            'controller_name' => 'StoreController',
            'form' => $form->createView(),
            'new_form' => $newForm->createView(),
            'store_items' => $storeItemRepository->findAll(),
            'new_store_item' => $newStoreItem
        ]);
    }
}
