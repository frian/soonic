<?php

namespace App\Controller;

use App\Repository\AlbumRepository;
use App\Repository\ArtistRepository;
use App\Repository\ConfigRepository;
use App\Repository\SongRepository;
use App\Form\ConfigType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Application settings page controller.
 */
#[Route(path: 'settings')]
class SettingsController extends AbstractController
{
    /**
     * Displays collection statistics and the configuration form.
     */
    #[Route(path: '/', name: 'settings_index', methods: ['GET'])]
    public function index(
        SongRepository $songRepository,
        ArtistRepository $artistRepository,
        AlbumRepository $albumRepository,
        ConfigRepository $configRepository
    ): Response
    {
        $infos = [
            'songs' => $songRepository->count([]),
            'artists' => $artistRepository->count([]),
            'albums' => $albumRepository->count([]),
        ];

        $config = $configRepository->find(1);
        if (!$config) {
            throw $this->createNotFoundException('Config not found.');
        }
        $editForm = $this->createForm(ConfigType::class, $config);

        return $this->render('settings/index.html.twig', [
            'infos' => $infos,
            'edit_form' => $editForm->createView(),
        ]);
    }
}
