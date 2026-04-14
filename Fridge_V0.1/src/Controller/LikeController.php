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

final class LikeController extends AbstractController
{
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
