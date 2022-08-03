<?php
namespace App\EventSubscriber;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;

class LocaleSubscriber implements EventSubscriberInterface {
    private $defaultLocale;
    public function __construct() {
        $this->defaultLocale = 'en';
    }
    public function onKernelRequest(RequestEvent $event) {
        $request = $event->getRequest();
        if ($locale = $request->query->get('_locale')) {
            $request->getSession()->set('_locale', $locale);
        }
        $locale = $request->cookies->get('locale') ?? $this->defaultLocale;
        $request->setLocale($request->getSession()->get('_locale', $locale));
    }
    public function onKernelResponse(ResponseEvent $event) {
        $response = $event->getResponse();
        $request = $event->getRequest();
        if ($locale = $request->attributes->get('_locale')) {
            $cookie = new Cookie('locale', $locale, time() + 24*60*60);
            $response->headers->setCookie($cookie);
        }
    }
    public static function getSubscribedEvents(): array {
        return [
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
            KernelEvents::RESPONSE => [['onKernelResponse', 0]],
        ];
        
    }
}
?>