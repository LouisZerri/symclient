<?php

namespace App\Controller;

use App\Entity\Invoice;
use Doctrine\ORM\EntityManagerInterface;

class InvoiceIncrementationController
{
    public function __construct(private readonly EntityManagerInterface $manager)
    {
    }

    public function __invoke(Invoice $data): Invoice
    {
        $data->setChrono($data->getChrono() + 1);

        $this->manager->flush();

        return $data;
    }
}
