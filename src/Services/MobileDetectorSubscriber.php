<?php


namespace App\Services;


use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

class MobileDetectorSubscriber implements EventSubscriberInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();
        $userAgent = $request->headers->get('User-Agent');



        if ($userAgent) {
            $isMobile = stripos($userAgent, 'Mobile') !== false
                || stripos($userAgent, 'Android') !== false
                || stripos($userAgent, 'iPhone') !== false
                || stripos($userAgent, 'iPad') !== false;
        }

        // Partage avec tous les templates Twig
        $this->twig->addGlobal('isMobile', true); //Mettre true pour simuler un mobile
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'kernel.request' => 'onKernelRequest',

        ];
    }
}

