<?php

namespace AppBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Process\Process;


class ScanListener implements EventSubscriberInterface {


    /**
	 * Constructor.
	 *
	 * @param string $defaultLocale
	 */
    public function __construct(string $projectDir = '') {
        $this->projectDir = $projectDir;
    }


    public function onKernelTerminate($event)
    {

        $request = $event->getRequest();

        $_route  = $request->attributes->get('_route');
//
// print("1<br>");
//
//         if ($_route === 'scan') {
//
//             $projectDir = $this->projectDir . "/../";
//
//             $builder = new ProcessBuilder();
// print("2<br>");
//             $builder->setPrefix('/usr/bin/php'); // /usr/bin/php
//             $command = $builder->setArguments(array($projectDir.'/bin/console', 'soonic:scan'))
//                 ->getProcess()
//                 ->getCommandLine();
// print("3<br>");
//             $process = new Process($command);
// print("4<br>");
//             $process->run();
// print("5<br>");
        // }
    }

    public static function getSubscribedEvents()
    {
        return array(
            KernelEvents::TERMINATE => array('onKernelTerminate', -1024)
        );
    }
}
