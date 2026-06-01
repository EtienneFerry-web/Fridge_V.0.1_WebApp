<?php

namespace App\Controller;

use App\Entity\Commentaire;
use App\Entity\NoteRecette;
use App\Entity\Recette;
use App\Repository\CommentaireRepository;
use App\Repository\NoteRecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class CommentaireController extends AbstractController
{
    #[Route('/recette/{id}/commenter', name: 'app_commentaire_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function add(
        Recette                  $recette,
        Request                  $request,
        EntityManagerInterface   $em
    ): JsonResponse {
        $contenu = trim((string) $request->request->get('contenu', ''));

        if ($contenu === '' || mb_strlen($contenu) > 1000) {
            return new JsonResponse(['error' => 'Contenu invalide.'], 400);
        }

        $user = $this->getUser();

        $commentaire = new Commentaire();
        $commentaire->setContenu($contenu)
                    ->setUser($user)
                    ->setRecette($recette);

        $em->persist($commentaire);
        $em->flush();

        $username = $user->getStrUsername();
        $initiales = mb_strtoupper(mb_substr($username, 0, 1));

        return new JsonResponse([
            'id'        => $commentaire->getId(),
            'contenu'   => $commentaire->getContenu(),
            'username'  => $username,
            'createdAt' => $commentaire->getCreatedAt()->format('d/m/Y'),
            'avatar'    => $initiales,
        ]);
    }

    #[Route('/recette/{id}/noter', name: 'app_note_add', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function noter(
        Recette                $recette,
        Request                $request,
        EntityManagerInterface $em,
        NoteRecetteRepository  $noteRepository
    ): JsonResponse {
        $valeur = (int) $request->request->get('valeur', 0);

        if ($valeur < 1 || $valeur > 5) {
            return new JsonResponse(['error' => 'Valeur invalide.'], 400);
        }

        $user = $this->getUser();
        $note = $noteRepository->findOneBy(['user' => $user, 'recette' => $recette]);

        if ($note) {
            $note->setValeur($valeur);
        } else {
            $note = new NoteRecette();
            $note->setValeur($valeur)
                 ->setUser($user)
                 ->setRecette($recette);
            $em->persist($note);
        }

        $em->flush();

        $average = $noteRepository->getAverageForRecette($recette);
        $count   = $noteRepository->count(['recette' => $recette]);

        return new JsonResponse([
            'valeur'  => $valeur,
            'average' => $average,
            'count'   => $count,
        ]);
    }

    #[Route('/commentaire/{id}/supprimer', name: 'app_commentaire_delete', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function delete(
        Commentaire            $commentaire,
        EntityManagerInterface $em
    ): JsonResponse {
        $user = $this->getUser();

        if ($commentaire->getUser() !== $user && !$this->isGranted('ROLE_MODERATOR')) {
            return new JsonResponse(['error' => 'Accès refusé.'], 403);
        }

        $em->remove($commentaire);
        $em->flush();

        return new JsonResponse(['deleted' => true]);
    }
}
