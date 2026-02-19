<?php

namespace App\Controller;

// use AppBundle\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Album controller.
 */
#[Route(path: 'scan')]
class ScanController extends AbstractController
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Scan.
     */
    #[Route(path: '/', name: 'scan', methods: ['GET'])]
    public function scan(): Response
    {
        $projectDir = $this->projectDir;
        $lockFile = $projectDir.'/public/soonic.lock';

        $command = $projectDir.'/bin/console soonic:scan';

        if (!file_exists($lockFile)) {
            exec("/usr/bin/php $command > /dev/null 2>&1 &");
        }

        return new Response('');
    }

    /**
     * Scan progress.
     */
    #[Route(path: '/progress', name: 'scan_progress', methods: ['GET'])]
    public function scanProgress(): Response
    {
        $projectDir = $this->projectDir;
        $lockFile = $projectDir.'/public/soonic.lock';
        $status = 'stopped';

        if (file_exists($lockFile)) {
            $status = 'running';
        }

        $files = ['song', 'artist', 'album'];
        $data = [];
        foreach ($files as $file) {
            $filePath = $projectDir.'/public/soonic-'.$file.'.sql';
            if (file_exists($filePath)) {
                $file_handle = new \SplFileObject($projectDir.'/public/soonic-'.$file.'.sql', 'r');
                $file_handle->seek(PHP_INT_MAX);
                $data[$file] = $file_handle->key() - 1;
            } else {
                $data[$file] = 0;
            }
        }

        $response = ['status' => $status, 'data' => $data];

        return new JsonResponse($response);
    }
}
