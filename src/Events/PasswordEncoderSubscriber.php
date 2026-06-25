<?php

namespace App\Events;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\User;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class PasswordEncoderSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly UserPasswordHasherInterface $hasher)
    {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['encodePassword', EventPriorities::PRE_WRITE],
        ];
    }

    public function encodePassword(ViewEvent $event): void
    {
        $user = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if ($user instanceof User && $method === 'POST') {
            $hash = $this->hasher->hashPassword($user, $user->getPassword());
            $user->setPassword($hash);
        }
    }
}
