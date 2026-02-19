<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Language management controller.
 *
 * Note: CRUD actions are intentionally commented out for now.
 */
#[Route(path: '/language')]
class LanguageController extends AbstractController
{
    // Legacy scaffold: index()
    // public function index(LanguageRepository $languageRepository): Response
    // {
    //     return $this->render('language/index.html.twig', [
    //         'languages' => $languageRepository->findAll(),
    //     ]);
    // }

    // Legacy scaffold: new()
    // public function new(Request $request): Response
    // {
    //     $language = new Language();
    //     $form = $this->createForm(LanguageType::class, $language);
    //     $form->handleRequest($request);
    //
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->persist($language);
    //         $entityManager->flush();
    //
    //         return $this->redirectToRoute('language_index');
    //     }
    //
    //     return $this->render('language/new.html.twig', [
    //         'language' => $language,
    //         'form' => $form->createView(),
    //     ]);
    // }

    // Legacy scaffold: show()
    // public function show(Language $language): Response
    // {
    //     return $this->render('language/show.html.twig', [
    //         'language' => $language,
    //     ]);
    // }

    // Legacy scaffold: edit()
    // public function edit(Request $request, Language $language): Response
    // {
    //     $form = $this->createForm(LanguageType::class, $language);
    //     $form->handleRequest($request);
    //
    //     if ($form->isSubmitted() && $form->isValid()) {
    //         $this->getDoctrine()->getManager()->flush();
    //
    //         return $this->redirectToRoute('language_index');
    //     }
    //
    //     return $this->render('language/edit.html.twig', [
    //         'language' => $language,
    //         'form' => $form->createView(),
    //     ]);
    // }

    // Legacy scaffold: delete()
    // public function delete(Request $request, Language $language): Response
    // {
    //     if ($this->isCsrfTokenValid('delete'.$language->getId(), $request->request->get('_token'))) {
    //         $entityManager = $this->getDoctrine()->getManager();
    //         $entityManager->remove($language);
    //         $entityManager->flush();
    //     }
    //
    //     return $this->redirectToRoute('language_index');
    // }
}
