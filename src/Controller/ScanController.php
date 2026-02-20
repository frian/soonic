<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Process\Process;

/**
 * Library scan orchestration controller.
 */
#[Route(path: '/scan')]
class ScanController extends AbstractController
{
    private const LOCK_FILE = '/var/lock/soonic.lock';
    private const LEGACY_LOCK_FILE = '/public/soonic.lock';
    private const SCAN_DIR = '/var/scan';
    private const LEGACY_SCAN_DIR = '/public';

    private string $projectDir;

    /**
     * @param string $projectDir Symfony project directory.
     */
    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    /**
     * Starts the asynchronous library scan command.
     */
    #[Route(path: '/', name: 'scan', methods: ['POST'])]
    public function scan(Request $request): JsonResponse
    {
        if (!$request->isXmlHttpRequest()) {
            return new JsonResponse(['status' => 'error', 'message' => 'invalid_request'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $csrfToken = (string) ($request->headers->get('X-CSRF-Token') ?? $request->request->get('_token') ?? '');
        if (!$this->isCsrfTokenValid('scan_action', $csrfToken)) {
            return new JsonResponse(['status' => 'error', 'message' => 'invalid_csrf_token'], JsonResponse::HTTP_FORBIDDEN);
        }

        if ($this->isScanRunning()) {
            return new JsonResponse(['status' => 'already_running']);
        }

        $consolePath = $this->projectDir.'/bin/console';
        if (!is_file($consolePath)) {
            return new JsonResponse(['status' => 'error', 'message' => 'console_not_found'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        $environment = (string) $this->getParameter('kernel.environment');
        $process = new Process([
            PHP_BINARY,
            $consolePath,
            'soonic:scan',
            '--no-interaction',
            '--env='.$environment,
        ], $this->projectDir);
        $process->disableOutput();

        try {
            $process->start();
        } catch (\Throwable) {
            return new JsonResponse(['status' => 'error', 'message' => 'scan_start_failed'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['status' => 'started']);
    }

    /**
     * Returns current scan status and temporary SQL file counters.
     */
    #[Route(path: '/progress', name: 'scan_progress', methods: ['GET'])]
    public function scanProgress(): JsonResponse
    {
        $status = $this->isScanRunning() ? 'running' : 'stopped';

        $files = ['song', 'artist', 'album'];
        $data = [];
        foreach ($files as $file) {
            $filePath = $this->resolveScanFilePath($file);
            if ($filePath !== null) {
                $file_handle = new \SplFileObject($filePath, 'r');
                $file_handle->seek(PHP_INT_MAX);
                $data[$file] = $file_handle->key() - 1;
            } else {
                $data[$file] = 0;
            }
        }

        $response = ['status' => $status, 'data' => $data];

        return new JsonResponse($response);
    }

    /**
     * Checks whether scan lock files are present.
     */
    private function isScanRunning(): bool
    {
        return file_exists($this->projectDir.self::LOCK_FILE)
            || file_exists($this->projectDir.self::LEGACY_LOCK_FILE);
    }

    private function resolveScanFilePath(string $file): ?string
    {
        $paths = [
            $this->projectDir.self::SCAN_DIR.'/soonic-'.$file.'.sql',
            $this->projectDir.self::LEGACY_SCAN_DIR.'/soonic-'.$file.'.sql',
        ];

        foreach ($paths as $path) {
            if (is_file($path)) {
                return $path;
            }
        }

        return null;
    }
}
