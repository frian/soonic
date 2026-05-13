<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\PhpExecutableFinder;

/**
 * Library scan orchestration controller.
 */
#[Route(path: '/scan')]
class ScanController extends AbstractController
{
    private const LOCK_FILE = '/var/lock/soonic.lock';
    private const LEGACY_LOCK_FILE = '/public/soonic.lock';
    private const PROGRESS_FILE = '/var/scan/soonic-progress.json';

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

        $phpBinary = (new PhpExecutableFinder())->find(false);
        if ($phpBinary === false || $phpBinary === '') {
            $phpBinary = 'php';
        }

        $environment = (string) $this->getParameter('kernel.environment');
        $command = $this->buildDetachedScanCommand($phpBinary, $consolePath, $environment);
        $process = Process::fromShellCommandline($command, $this->projectDir);
        $process->setTimeout(10);

        try {
            $process->run();
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
        $progressSnapshot = $this->readProgressSnapshot();
        if ($progressSnapshot !== null && isset($progressSnapshot['status'], $progressSnapshot['data']) && \is_string($progressSnapshot['status']) && \is_array($progressSnapshot['data'])) {
            return new JsonResponse([
                'status' => $progressSnapshot['status'],
                'data' => [
                    'song' => (int) ($progressSnapshot['data']['song'] ?? 0),
                    'artist' => (int) ($progressSnapshot['data']['artist'] ?? 0),
                    'album' => (int) ($progressSnapshot['data']['album'] ?? 0),
                ],
            ]);
        }

        return new JsonResponse([
            'status' => $status,
            'data' => [
                'song' => 0,
                'artist' => 0,
                'album' => 0,
            ],
        ]);
    }

    /**
     * Checks whether scan lock files are present.
     */
    private function isScanRunning(): bool
    {
        return file_exists($this->projectDir.self::LOCK_FILE)
            || file_exists($this->projectDir.self::LEGACY_LOCK_FILE);
    }

    private function readProgressSnapshot(): ?array
    {
        $progressPath = $this->projectDir.self::PROGRESS_FILE;
        if (!is_file($progressPath)) {
            return null;
        }

        $raw = @file_get_contents($progressPath);
        if ($raw === false || $raw === '') {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (!\is_array($decoded)) {
            return null;
        }

        return $decoded;
    }

    private function buildDetachedScanCommand(string $phpBinary, string $consolePath, string $environment): string
    {
        if (\PHP_OS_FAMILY === 'Windows') {
            $psPhpBinary = str_replace("'", "''", $phpBinary);
            $psConsolePath = str_replace("'", "''", $consolePath);
            $psEnvironment = str_replace("'", "''", $environment);

            return sprintf(
                "powershell -NoProfile -NonInteractive -ExecutionPolicy Bypass -Command \"Start-Process -FilePath '%s' -ArgumentList @('%s','soonic:scan','--no-interaction','--env=%s') -WindowStyle Hidden\"",
                $psPhpBinary,
                $psConsolePath,
                $psEnvironment
            );
        }

        return sprintf(
            'nohup %s %s soonic:scan --no-interaction --env=%s > /dev/null 2>&1 &',
            escapeshellarg($phpBinary),
            escapeshellarg($consolePath),
            escapeshellarg($environment)
        );
    }
}
