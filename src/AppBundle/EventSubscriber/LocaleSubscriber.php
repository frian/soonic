<?php

namespace AppBundle\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use AppBundle\Entity\Config;

class LocaleSubscriber implements EventSubscriberInterface
{
    private $defaultLocale;

    /**
	 * Entity Manager
	 *
	 * @var EntityManager $em
	 */
	protected $em;

	/**
	 * Constructor
	 *
	 * @param EntityManager $em
	 */
	public function __construct($defaultLocale = 'en', \Doctrine\ORM\EntityManager $em) {
		$this->em = $em;
		$this->defaultLocale = $defaultLocale;
	}



    public function onKernelRequest(GetResponseEvent $event)
    {
        $request = $event->getRequest();

        $config = $this->em->getRepository('AppBundle:Config')->find(1);
        $lang = $config->getLanguage();

        $request->getSession()->set('_locale', $lang);

        $request->setLocale($request->getSession()->get('_locale', $lang));
    }

    public static function getSubscribedEvents()
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
