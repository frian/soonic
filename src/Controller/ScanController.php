<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Process\Process;

/**
 * Album controller.
 */
#[Route(path: 'scan')]
class ScanController extends AbstractController
{
    private const LOCK_FILE = '/var/lock/soonic.lock';
    private const LEGACY_LOCK_FILE = '/public/soonic.lock';

    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Scan.
     */
    #[Route(path: '/', name: 'scan', methods: ['POST'])]
    public function scan(): JsonResponse
    {
        if ($this->isScanRunning()) {
            return new JsonResponse(['status' => 'already_running']);
        }

        $consolePath = $this->projectDir.'/bin/console';
        if (!is_file($consolePath)) {
            return new JsonResponse(['status' => 'error', 'message' => 'console_not_found'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $environment = (string) $this->getParameter('kernel.environment');
        $command = sprintf(
            'nohup %s %s soonic:scan --no-interaction --env=%s > /dev/null 2>&1 &',
            escapeshellarg(PHP_BINARY),
            escapeshellarg($consolePath),
            escapeshellarg($environment)
        );

        $process = Process::fromShellCommandline($command, $this->projectDir);
        $process->run();

        if (!$process->isSuccessful()) {
            return new JsonResponse(['status' => 'error', 'message' => 'scan_start_failed'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'started']);
    }

    /**
     * Scan progress.
     */
    #[Route(path: '/progress', name: 'scan_progress', methods: ['GET'])]
    public function scanProgress(): JsonResponse
    {
        $status = $this->isScanRunning() ? 'running' : 'stopped';

        $files = ['song', 'artist', 'album'];
        $data = [];
        foreach ($files as $file) {
            $filePath = $this->projectDir.'/public/soonic-'.$file.'.sql';
            if (file_exists($filePath)) {
                $file_handle = new \SplFileObject($this->projectDir.'/public/soonic-'.$file.'.sql', 'r');
                $file_handle->seek(PHP_INT_MAX);
                $data[$file] = $file_handle->key() - 1;
            } else {
                $data[$file] = 0;
            }
        }

        $response = ['status' => $status, 'data' => $data];

        return new JsonResponse($response);
    }

    private function isScanRunning(): bool
    {
        return file_exists($this->projectDir.self::LOCK_FILE)
            || file_exists($this->projectDir.self::LEGACY_LOCK_FILE);
    }
}
