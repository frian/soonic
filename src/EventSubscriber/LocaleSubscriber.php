<?php

namespace App\EventSubscriber;

use App\Entity\Config;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocaleSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly ManagerRegistry $doctrine)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 20],
        ];
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        if (!$event->isMainRequest()) {
            return;
        }

        $config = $this->doctrine->getRepository(Config::class)->find(1);
        if (!$config || !$config->getLanguage()) {
            return;
        }

        $locale = $config->getLanguage()->getCode();
        if (!$locale) {
            return;
        }

        $event->getRequest()->setLocale($locale);
    }
}
