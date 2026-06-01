<?php

namespace App\Repository;

use App\Entity\NoteRecette;
use App\Entity\Recette;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<NoteRecette>
 */
class NoteRecetteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, NoteRecette::class);
    }

    public function getAverageForRecette(Recette $recette): float
    {
        $result = $this->createQueryBuilder('n')
            ->select('AVG(n.valeur) as avg')
            ->where('n.recette = :recette')
            ->setParameter('recette', $recette)
            ->getQuery()
            ->getSingleScalarResult();

        return $result ? round((float) $result, 1) : 0.0;
    }
}
