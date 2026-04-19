<?php

namespace App\Controller;


use App\Entity\Favori;
use App\Entity\Recette;
use App\Repository\FavoriRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur de gestion des favoris.
 *
 * Permet à un utilisateur connecté d'ajouter ou retirer une recette de ses favoris.
 */
final class FavoriController extends AbstractController
{
    /**
     * Bascule l'état favori d'une recette pour l'utilisateur connecté.
     *
     * Retourne une réponse JSON avec le nouvel état et le nombre total de favoris.
     *
     * @param Recette                $objRecette       La recette concernée
     * @param FavoriRepository       $favoriRepository Repository des favoris
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
    #[Route('/recette/{id}/favori', name: 'app_favori_toggle', methods: ['POST'])]
    public function toggle(
        Recette $objRecette,
        FavoriRepository $favoriRepository,
        EntityManagerInterface $objEntityManager
    ): JsonResponse {    
        $this->denyAccessUnlessGranted('ROLE_USER');

        $objUser = $this->getUser();
        $objFavori = $favoriRepository->findOneBy([
            'favoriUser' => $objUser, 
            'favoriRecette' => $objRecette,
        ]);

        if($objFavori) {
            $objEntityManager->remove($objFavori);
            $boolFavori = false;
        } else {
            $objFavori = new Favori();
            $objFavori->setFavoriUser($objUser)
                      ->setFavoriRecette($objRecette)
                      ->setFavoriDate(new \DateTimeImmutable());
            $objEntityManager->persist($objFavori);
            $boolFavori = true;
        }

        $objEntityManager->flush();

        $intCount = $favoriRepository->count(['favoriRecette' => $objRecette]);
        
            return new JsonResponse([
                'favori' => $boolFavori, 
                'count' => $intCount
            ]);
     
        }
    }