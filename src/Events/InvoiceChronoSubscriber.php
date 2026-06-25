<?php

namespace App\Events;

use ApiPlatform\Symfony\EventListener\EventPriorities;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class InvoiceChronoSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly InvoiceRepository $repository,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::VIEW => ['setChronoForInvoice', EventPriorities::PRE_VALIDATE],
        ];
    }

    public function setChronoForInvoice(ViewEvent $event): void
    {
        $invoice = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        // On n'agit qu'à la création d'une facture (chrono pas encore défini),
        // pas sur l'opération custom "increment" qui réutilise un POST.
        if ($invoice instanceof Invoice && $method === 'POST' && $invoice->getChrono() === null) {
            $nextChrono = $this->repository->findNextChrono($this->security->getUser());
            $invoice->setChrono($nextChrono);

            if (empty($invoice->getSentAt())) {
                $invoice->setSentAt(new \DateTime());
            }
        }
    }
}
