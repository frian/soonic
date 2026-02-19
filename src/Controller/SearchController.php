<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\SongRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
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

        return $this->render('common/search.html.twig', [
            'form' => $form,
        ]);
    }
}
