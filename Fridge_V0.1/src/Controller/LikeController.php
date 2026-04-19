<?php

namespace App\Controller;

use App\Entity\LikeRecette;
use App\Entity\Recette;
use App\Entity\User;
use App\Repository\LikeRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de gestion des likes sur les recettes.
 *
 * Permet à un utilisateur connecté d'ajouter ou retirer un like sur une recette.
 */
final class LikeController extends AbstractController
{
    /**
     * Bascule l'état like d'une recette pour l'utilisateur connecté.
     *
     * Retourne une réponse JSON avec le nouvel état et le nombre total de likes.
     *
     * @param Recette                $objRecette       La recette concernée
     * @param LikeRecetteRepository  $likeRepository   Repository des likes
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
    #[Route('/recette/{id}/like', name: 'app_like_toggle', methods: ['POST'])]
    public function toggle(
        Recette $objRecette,
        LikeRecetteRepository $likeRepository,
        EntityManagerInterface $objEntityManager
    ): JsonResponse {    
        $this->denyAccessUnlessGranted('ROLE_USER');

        $objUser = $this->getUser();
        $objLike = $likeRepository->findOneBy([
            'likeUser' => $objUser, 
            'likeRecette' => $objRecette,
        ]);

        if($objLike) {
            $objEntityManager->remove($objLike);
            $boolLiked = false;
        } else {
            $objLike = new LikeRecette();
            $objLike->setLikeUser($objUser)
                    ->setLikeRecette($objRecette)
                    ->setLikeDate(new \DateTimeImmutable());
            $objEntityManager->persist($objLike);
            $boolLiked = true;
        }

        $objEntityManager->flush();

        $intCount = $likeRepository->count(['likeRecette' => $objRecette]);
        
            return new JsonResponse([
                'liked' => $boolLiked,
                'count' => $intCount
            ]);
     
        }
    }
