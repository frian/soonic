<?php

namespace App\Controller;

use App\Form\ConfigType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Settings controller.
 */
#[Route(path: 'settings')]
class SettingsController extends AbstractController
{
    /**
     * Show settings page.
     */
    #[Route(path: '/', name: 'settings_index', methods: ['GET'])]
    public function index(ManagerRegistry $doctrine, Request $request): Response
    {
        // -- get collection infos
        $tables = ['song', 'artist', 'album'];
        $infos = [];

        foreach ($tables as $table) {
            $query = "select max(id) from $table";
            $statement = $doctrine->getConnection()->prepare($query);
            $result = $statement->executeQuery()->fetchOne();
            if ($result === null) {
                $result = 0;
            }
            \array_push($infos, $result);
        }

        // -- get config form
        $config = $doctrine->getRepository('App\Entity\Config')->find(1);
        if (! $config) {
            die;
        }
        $editForm = $this->createForm(ConfigType::class, $config);
        $editForm->handleRequest($request);

        return $this->render('settings/index.html.twig', [
            'infos' => $infos,
            'edit_form' => $editForm,
        ]);
    }
}
