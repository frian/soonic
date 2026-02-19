<?php

namespace App\Controller;

use App\Entity\Config;
use App\Form\ConfigType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route(path: '/config')]
class ConfigController extends AbstractController
{
    /**
     * Method edit
     *
     * @param EntityManagerInterface $entityManager
     * @param Request $request
     * @param Config $config
     *
     * @return JsonResponse
     */
    #[Route(path: '/{id}/edit', name: 'config_edit', methods: ['POST'])]
    public function edit(EntityManagerInterface $entityManager, Request $request, Config $config): JsonResponse|RedirectResponse
    {
        $form = $this->createForm(ConfigType::class, $config);
        $form->handleRequest($request);

        if (!$form->isSubmitted()) {
            return new JsonResponse(['status' => 'error', 'message' => 'form_not_submitted'], JsonResponse::HTTP_BAD_REQUEST);
        }

        if (!$form->isValid()) {
            return new JsonResponse(['status' => 'error', 'message' => 'invalid_form'], JsonResponse::HTTP_UNPROCESSABLE_ENTITY);
        }

        $entityManager->flush();
        $theme = $config->getTheme()?->getName();
        $lang = $config->getLanguage()?->getCode();

        if (!$theme || !$lang) {
            return new JsonResponse(['status' => 'error', 'message' => 'missing_config'], JsonResponse::HTTP_BAD_REQUEST);
        }

        return new JsonResponse([
                'status' => 'success',
                'config' => [
                    'theme' => $theme,
                    'language' => $lang,
                ],
            ]);
    }
}
