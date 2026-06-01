<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Entity\Recette;
use App\Repository\ListeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class ListeController extends AbstractController
{
    private const MAX_LISTES = 10;

    /**
     * Page "toutes mes listes".
     */
    #[Route('/mes-listes', name: 'app_liste_index')]
    public function index(ListeRepository $listeRepository): Response
    {
        $arrListes = $listeRepository->findBy(['user' => $this->getUser()], ['id' => 'DESC']);

        return $this->render('liste/index.html.twig', [
            'listes' => $arrListes,
        ]);
    }

    /**
     * Retourne les listes de l'utilisateur + si la recette est déjà dedans (pour le modal).
     */
    #[Route('/liste/mes-listes/{recetteId}', name: 'app_liste_for_recette', methods: ['GET'])]
    public function forRecette(int $recetteId, ListeRepository $listeRepository): JsonResponse
    {
        $objUser   = $this->getUser();
        $arrListes = $listeRepository->findBy(['user' => $objUser], ['id' => 'DESC']);

        $arrData = array_map(fn(Liste $l) => [
            'id'       => $l->getId(),
            'nom'      => $l->getNom(),
            'contains' => $l->getRecettes()->exists(fn($k, Recette $r) => $r->getId() === $recetteId),
            'count'    => $l->getRecettes()->count(),
        ], $arrListes);

        return new JsonResponse([
            'listes'    => $arrData,
            'canCreate' => count($arrListes) < self::MAX_LISTES,
        ]);
    }

    /**
     * Crée une nouvelle liste et y ajoute optionnellement une recette.
     */
    #[Route('/liste/nouvelle', name: 'app_liste_create', methods: ['POST'])]
    public function create(
        Request                $objRequest,
        EntityManagerInterface $em,
        ListeRepository        $listeRepository
    ): JsonResponse {
        $objUser = $this->getUser();

        if ($listeRepository->count(['user' => $objUser]) >= self::MAX_LISTES) {
            return new JsonResponse(['error' => 'Limite de ' . self::MAX_LISTES . ' listes atteinte.'], 400);
        }

        $strNom = trim((string) $objRequest->request->get('nom', ''));
        if ($strNom === '') {
            return new JsonResponse(['error' => 'Le nom de la liste est requis.'], 400);
        }

        $objListe = new Liste();
        $objListe->setNom($strNom)->setUser($objUser);
        $em->persist($objListe);

        $intRecetteId = (int) $objRequest->request->get('recette_id', 0);
        if ($intRecetteId > 0) {
            $objRecette = $em->find(Recette::class, $intRecetteId);
            if ($objRecette) {
                $objListe->addRecette($objRecette);
            }
        }

        $em->flush();

        return new JsonResponse([
            'id'       => $objListe->getId(),
            'nom'      => $objListe->getNom(),
            'contains' => $intRecetteId > 0,
            'count'    => $objListe->getRecettes()->count(),
        ], 201);
    }

    /**
     * Ajoute ou retire une recette d'une liste (toggle).
     */
    #[Route('/liste/{id}/toggle/{recetteId}', name: 'app_liste_toggle', methods: ['POST'])]
    public function toggle(
        Liste                  $objListe,
        int                    $recetteId,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($objListe->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        $objRecette = $em->find(Recette::class, $recetteId);
        if (!$objRecette) {
            return new JsonResponse(['error' => 'Recette introuvable.'], 404);
        }

        if ($objListe->getRecettes()->contains($objRecette)) {
            $objListe->removeRecette($objRecette);
            $boolContains = false;
        } else {
            $objListe->addRecette($objRecette);
            $boolContains = true;
        }

        $em->flush();

        return new JsonResponse([
            'contains' => $boolContains,
            'count'    => $objListe->getRecettes()->count(),
        ]);
    }

    /**
     * Supprime une liste entière.
     */
    #[Route('/liste/{id}/supprimer', name: 'app_liste_delete', methods: ['POST'])]
    public function delete(
        Liste                  $objListe,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($objListe->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        $em->remove($objListe);
        $em->flush();

        return new JsonResponse(['deleted' => true]);
    }
}
