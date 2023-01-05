<?php

namespace App\Controller;

use App\Entity\Score;
use App\Form\ScoreType;
use App\Repository\ScoreRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/score')]
class ScoreController extends AbstractController
{
    #[Route('/', name: 'app_score_index', methods: ['GET'])]
    public function index(ScoreRepository $scoreRepository): Response
    {
        return $this->render('score/index.html.twig', [
            'scores' => $scoreRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_score_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ScoreRepository $scoreRepository): Response
    {
        $score = new Score();
        $form = $this->createForm(ScoreType::class, $score);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scoreRepository->save($score, true);

            return $this->redirectToRoute('app_score_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('score/new.html.twig', [
            'score' => $score,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_score_show', methods: ['GET'])]
    public function show(Score $score): Response
    {
        return $this->render('score/show.html.twig', [
            'score' => $score,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_score_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Score $score, ScoreRepository $scoreRepository): Response
    {
        $form = $this->createForm(ScoreType::class, $score);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $scoreRepository->save($score, true);

            return $this->redirectToRoute('app_score_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('score/edit.html.twig', [
            'score' => $score,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_score_delete', methods: ['POST'])]
    public function delete(Request $request, Score $score, ScoreRepository $scoreRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$score->getId(), $request->request->get('_token'))) {
            $scoreRepository->remove($score, true);
        }

        return $this->redirectToRoute('app_score_index', [], Response::HTTP_SEE_OTHER);
    }
}
