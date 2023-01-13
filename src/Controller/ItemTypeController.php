<?php

namespace App\Controller;

use App\Entity\Effect;
use App\Entity\ItemType;
use App\Form\ItemTypeType;
use App\Form\MultipleItemType;
use App\Repository\ItemTypeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;

#[Route('/admin/item/type')]
class ItemTypeController extends AbstractController
{
    #[Route('/', name: 'app_item_type_index', methods: ['GET'])]
    public function index(ItemTypeRepository $itemTypeRepository): Response
    {
        return $this->render('item_type/index.html.twig', [
            'item_types' => $itemTypeRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_item_type_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ItemTypeRepository $itemTypeRepository): Response
    {
        $itemType = new ItemType();
        $form = $this->createForm(ItemTypeType::class, $itemType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemTypeRepository->save($itemType, true);

            return $this->redirectToRoute('app_item_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('item_type/new.html.twig', [
            'item_type' => $itemType,
            'form' => $form,
        ]);
    }

    #[Route('/add_items', name: 'app_item_type_add_items', methods: ['POST'])]
    public function addItems(Request $request, ItemTypeRepository $itemTypeRepository, SluggerInterface $slugger): Response
    {
        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        if (isset($file)) {
            $content = $file->getContent();

            $items = explode("\n", $content);

            if (str_starts_with($items[0], "name")) {
                array_shift($items);
            }

            foreach ($items as $item) {
                if (!str_starts_with($item, "\t\t\t")) {

                    $data = explode("\t", $item);

                    $isExistItem = $itemTypeRepository->findOneBy(["name" => $data[0]]);

                    if (!$isExistItem) {
                        $newItem = new ItemType();
                        $newItem->setName($data[0]);
                        $newItem->setDescription($data[1]);
                        $newItem->setType($data[2]);
                        $newItem->setRarity($data[3]);
                        $newItem->setFantastic($data[4]);
                        $newItem->setImg($data[7]);

                        $effects = [];

                        if (str_contains($data[5], ",")) {
                            $effectsType = explode(",", $data[5]);
                            $effectsValue = explode(",", $data[6]);

                            for ($i = 0; $i < sizeof($effectsType); $i++) {
                                $newEffect = new Effect();
                                $newEffect->setType($effectsType[$i]);
                                $newEffect->setValue($effectsValue[$i]);

                                $effects[] = $newEffect;
                            }

                            if (str_contains($newItem->getDescription(), "%d")) {
                                $newItem->setDescription(sprintf($newItem->getDescription(), ...$effectsValue));
                            }
                        } else {
                            $newEffect = new Effect();
                            $newEffect->setType($data[5]);
                            $newEffect->setValue($data[6]);

                            $effects[] = $newEffect;

                            if (str_contains($newItem->getDescription(), "%d")) {
                                $newItem->setDescription(sprintf($newItem->getDescription(), $newEffect->getValue()));
                            }
                        }

                        $newItem->setEffects($effects);

                        $itemTypeRepository->save($newItem, true);
                    }
                }
            }
        }

        return $this->redirectToRoute('app_item_type_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}', name: 'app_item_type_show', methods: ['GET'])]
    public function show(ItemType $itemType): Response
    {
        return $this->render('item_type/show.html.twig', [
            'item_type' => $itemType,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_item_type_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ItemType $itemType, ItemTypeRepository $itemTypeRepository): Response
    {
        $form = $this->createForm(ItemTypeType::class, $itemType);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $itemTypeRepository->save($itemType, true);

            return $this->redirectToRoute('app_item_type_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('item_type/edit.html.twig', [
            'item_type' => $itemType,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_item_type_delete', methods: ['POST'])]
    public function delete(Request $request, ItemType $itemType, ItemTypeRepository $itemTypeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$itemType->getId(), $request->request->get('_token'))) {
            $itemTypeRepository->remove($itemType, true);
        }

        return $this->redirectToRoute('app_item_type_index', [], Response::HTTP_SEE_OTHER);
    }
}
