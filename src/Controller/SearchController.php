<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Song search controller.
 */
class SearchController extends AbstractController
{
    /**
     * Renders the search form or returns matching songs list on submit.
     */
    #[Route(path: '/search', name: 'search', methods: ['GET', 'POST'])]
    public function showSearch(SongRepository $songRepository, Request $request): Response
    {
        $form = $this->createForm(SearchType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $keyword = trim((string) $form->get('keyword')->getData());

            if ($keyword === '') {
                return $this->render('common/songs-list.html.twig', [
                    'songs' => [],
                ]);
            }

            return $this->render('common/songs-list.html.twig', [
                'songs' => $songRepository->findByKeyword($keyword),
            ]);
        }

        $statusCode = $form->isSubmitted() ? Response::HTTP_UNPROCESSABLE_ENTITY : Response::HTTP_OK;

        return $this->render('common/search.html.twig', [
            'search_form' => $form->createView(),
        ], new Response('', $statusCode));
    }
}
