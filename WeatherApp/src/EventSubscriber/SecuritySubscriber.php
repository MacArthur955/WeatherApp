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

class SecuritySubscriber implements EventSubscriberInterface {
    public function __construct(RequestStack $requestStack) {
        $this->requestStack = $requestStack;
    }
    public function onLoginSuccess(LoginSuccessEvent $event) {
        $user = $event->getAuthenticatedToken()->getUser();
        $this->requestStack->getSession()->set('_locale', $user->getLocale());
    }
    public static function getSubscribedEvents(): array {
        return [
            LoginSuccessEvent::class => [['onLoginSuccess']],
        ];
        
    }
}
?>