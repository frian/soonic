<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Album;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\HttpFoundation\Response;


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

        exec("nohup /usr/bin/php -f /home/lpa/atinfo/www/subsonic/bin/console soonic:scan > /dev/null 2>&1 &");

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



        $file = new \SplFileObject($this->get('kernel')->getProjectDir().'/web/soonic.sql', 'r');
        $file->seek(PHP_INT_MAX);

        // echo $file->key() + 1;

        return new Response($file->key() + 1);

        // return $this->render('settings/index.html.twig', array(
        //     'data' => $file->key() + 1
        // ));
    }

}
