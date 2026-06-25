<?php

namespace App\Command;

use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * Crée le compte de démonstration et un petit jeu de données.
 * Idempotent : ne fait rien si le compte de démo existe déjà.
 * Utilisable en production (contrairement aux fixtures, réservées au dev).
 */
#[AsCommand(
    name: 'app:seed-demo',
    description: 'Crée le compte de démonstration (demo@symreact.local / password) et des données exemple',
)]
class SeedDemoCommand extends Command
{
    private const DEMO_EMAIL = 'demo@symreact.local';
    private const DEMO_PASSWORD = 'password';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly UserRepository $userRepository,
        private readonly UserPasswordHasherInterface $hasher,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($this->userRepository->findOneBy(['email' => self::DEMO_EMAIL])) {
            $io->info('Le compte de démonstration existe déjà — rien à faire.');

            return Command::SUCCESS;
        }

        $demo = new User();
        $demo->setFirstName('Demo')
            ->setLastName('User')
            ->setEmail(self::DEMO_EMAIL)
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword($this->hasher->hashPassword($demo, self::DEMO_PASSWORD));
        $this->em->persist($demo);

        $statuses = ['SENT', 'PAID', 'CANCELLED'];
        $samples = [
            ['Marie', 'Durand', 'marie.durand@example.com', 'Durand SARL'],
            ['Paul', 'Martin', 'paul.martin@example.com', 'Martin & Fils'],
            ['Sophie', 'Bernard', 'sophie.bernard@example.com', 'Bernard Consulting'],
        ];

        $chrono = 1;
        foreach ($samples as [$first, $last, $email, $company]) {
            $customer = new Customer();
            $customer->setFirstName($first)
                ->setLastName($last)
                ->setEmail($email)
                ->setCompany($company)
                ->setUser($demo);
            $this->em->persist($customer);

            for ($i = 0; $i < 4; ++$i) {
                $invoice = new Invoice();
                $invoice->setAmount(round(500 + ($chrono * 137.5), 2))
                    ->setSentAt(new \DateTime(sprintf('-%d days', $i * 9)))
                    ->setStatus($statuses[$chrono % 3])
                    ->setChrono($chrono)
                    ->setCustomer($customer);
                $this->em->persist($invoice);
                ++$chrono;
            }
        }

        $this->em->flush();

        $io->success(sprintf(
            'Compte de démo créé : %s / %s (avec %d clients et %d factures).',
            self::DEMO_EMAIL,
            self::DEMO_PASSWORD,
            count($samples),
            $chrono - 1,
        ));

        return Command::SUCCESS;
    }
}
