<?php

namespace App\Doctrine;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Extension\QueryItemExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use App\Entity\Customer;
use App\Entity\Invoice;
use App\Entity\User;
use Doctrine\ORM\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class CurrentUserExtension implements QueryCollectionExtensionInterface, QueryItemExtensionInterface
{
    public function __construct(
        private readonly Security $security,
        private readonly AuthorizationCheckerInterface $auth,
    ) {
    }

    private function addWhere(QueryBuilder $queryBuilder, string $resourceClass): void
    {
        $user = $this->security->getUser();

        if (
            ($resourceClass === Customer::class || $resourceClass === Invoice::class)
            && !$this->auth->isGranted('ROLE_ADMIN')
            && $user instanceof User
        ) {
            $rootAlias = $queryBuilder->getRootAliases()[0];

            if ($resourceClass === Customer::class) {
                $queryBuilder->andWhere("$rootAlias.user = :user");
            } elseif ($resourceClass === Invoice::class) {
                $queryBuilder->join("$rootAlias.customer", 'c')
                    ->andWhere('c.user = :user');
            }

            $queryBuilder->setParameter('user', $user);
        }
    }

    public function applyToCollection(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }

    public function applyToItem(QueryBuilder $queryBuilder, QueryNameGeneratorInterface $queryNameGenerator, string $resourceClass, array $identifiers, ?Operation $operation = null, array $context = []): void
    {
        $this->addWhere($queryBuilder, $resourceClass);
    }
}
