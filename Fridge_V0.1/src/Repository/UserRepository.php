<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * Repository des utilisateurs.
 *
 * Implémente PasswordUpgraderInterface pour le re-hachage automatique des mots de passe.
 *
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Re-hache le mot de passe de l'utilisateur (appelé automatiquement par Symfony lors de la connexion si l'algorithme a changé).
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    /**
     * Recherche des utilisateurs dans le dashboard avec filtres optionnels sur le pseudo/email et le rôle.
     *
     * @param string $strQuery Terme de recherche (pseudo ou email, partiel)
     * @param string $strRole  Rôle à filtrer ('all' = pas de filtre)
     *
     * @return User[]
     */
    public function findByFilter(string $strQuery, string $strRole): array
    {
        $qb = $this->createQueryBuilder('u');

        if ($strQuery) {
            $qb->andWhere('u.strUsername LIKE :q OR u.strEmail LIKE :q')
            ->setParameter('q', '%' . $strQuery . '%');
        }

        if ($strRole !== 'all') {
            $qb->andWhere('u.arrRoles LIKE :role')
            ->setParameter('role', '%' . $strRole . '%');
        }

        return $qb->orderBy('u.dateInscription', 'DESC')->getQuery()->getResult();
    }
}
