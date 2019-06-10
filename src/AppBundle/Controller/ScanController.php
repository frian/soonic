<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Album controller.
 *
 * @Route("scan")
 */
class ScanController extends Controller {

    /**
     * Scan
     *
     * @Route("/", name="scan")
     * @Method("GET")
     */
    public function scanAction(KernelInterface $kernel) {

        $projectDir = $this->get('kernel')->getProjectDir();
        $command = $projectDir.'/bin/console soonic:scan --guess';

        // exec("nohup /usr/bin/php  $command > /dev/null 2>&1 &");
        exec("/usr/bin/php $command > /dev/null 2>&1 &");

        return new Response('');
    }

    /**
     * Scan progress
     *
     * @Route("/progress", name="scan_progress")
     * @Method("GET")
     */
    public function scanProgressAction() {

        $projectDir = $this->get('kernel')->getProjectDir();
        $lockFile = $projectDir.'/web/soonic.lock';
        $status = 'stopped';

        if (file_exists($lockFile)) {
            $status = 'running';
        }

        $files = array('media_file', 'artist', 'album');
        $data = array();
        foreach ($files as $file) {
            $file_handle = new \SplFileObject($this->get('kernel')->getProjectDir().'/web/soonic-'.$file.'.sql', 'r');
            $file_handle->seek(PHP_INT_MAX);
            $data[$file] = $file_handle->key() - 1;
        }

        $response = ['status' => $status, 'data' => $data];
        return new JsonResponse($response);
    }

}
