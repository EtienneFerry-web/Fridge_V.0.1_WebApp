<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Repository\CommentaireRepository;
use App\Repository\FavoriRepository;
use App\Repository\LikeRecetteRepository;
use App\Repository\NoteRecetteRepository;
use App\Repository\RecetteRepository;
use App\Security\Voter\RecetteVoter;
use App\Service\TheMealDbClient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * Contrôleur des recettes.
 *
 * Gère les recettes utilisateur (BDD, statut='prive'/'publie') : CRUD complet.
 * La découverte publique est assurée par TheMealDB (cf. mealDbShow).
 */
final class RecetteController extends AbstractController
{
    /**
     * Page Découvrir — filtres par catégorie, origine et tri.
     */
    #[Route('/recette', name: 'app_recette_index')]
    public function index(
        Request         $objRequest,
        TheMealDbClient $mealDbClient
    ): Response {
        $category = $objRequest->query->get('category', 'all');
        $area     = $objRequest->query->get('area', 'all');
        $sort     = $objRequest->query->get('sort', 'default');

        $arrRecettes = [];
        try {
            $arrRecettes = $mealDbClient->getFilteredMeals(0, $category, $area);

            if ($sort === 'random') {
                shuffle($arrRecettes);
            } elseif ($sort === 'alpha') {
                usort($arrRecettes, fn($a, $b) => strcmp($a['title'], $b['title']));
            }
        } catch (\Throwable) {
            $this->addFlash('error', 'Impossible de charger les recettes pour le moment.');
        }

        $availableCategories = $mealDbClient->getAvailableCategories();
        $availableAreas      = $mealDbClient->getAvailableAreas();

        return $this->render('recette/index.html.twig', [
            'recettes'            => $arrRecettes,
            'activeCategory'      => $category,
            'activeArea'          => $area,
            'activeSort'          => $sort,
            'availableCategories' => $availableCategories,
            'availableAreas'      => $availableAreas,
            'totalCount'          => count($arrRecettes),
        ]);
    }

    /**
     * Liste des recettes créées par l'utilisateur connecté (page perso "Mes recettes").
     *
     * @param RecetteRepository $objRepository Repository des recettes
     * @param Request           $objRequest    Requête HTTP (paramètres ?regime= et ?sort=)
     */
    #[Route('/mes-recettes', name: 'app_recette_mine')]
    #[IsGranted('ROLE_USER')]
    public function mine(
        RecetteRepository $objRepository,
        Request           $objRequest
    ): Response {
        $strRegime = $objRequest->query->get('regime', 'all');
        $strSort   = $objRequest->query->get('sort', 'recent');

        $arrRecettes = $objRepository->findUserRecettes(
            $this->getUser(),
            $strRegime,
            $strSort
        );

        return $this->render('recette/mine.html.twig', [
            'recettes'     => $arrRecettes,
            'activeRegime' => $strRegime,
            'activeSort'   => $strSort,
        ]);
    }

    /**
     * Affiche le détail d'une recette TheMealDB.
     */
    #[Route('/recette/mealdb/{mealId}', name: 'app_recette_mealdb_show', requirements: ['mealId' => '\d+'])]
    public function mealDbShow(int $mealId, TheMealDbClient $mealDbClient): Response
    {
        try {
            $meal = $mealDbClient->getMealById($mealId);
        } catch (\Throwable) {
            $meal = null;
        }

        if (!$meal) {
            $this->addFlash('error', 'Recette introuvable.');
            return $this->redirectToRoute('app_recette_index');
        }

        return $this->render('recette/mealdb_show.html.twig', ['meal' => $meal]);
    }

    /**
     * Affiche le détail d'une recette en BDD (user ou importée).
     *
     * @param Recette $objRecette La recette à afficher (résolue automatiquement)
     */
    #[Route('/recette/{id}', name: 'app_recette_show', requirements: ['id' => '\d+'])]
    public function show(
        Recette                $objRecette,
        LikeRecetteRepository  $likeRepository,
        NoteRecetteRepository  $noteRepository,
        CommentaireRepository  $commentaireRepository
    ): Response {
        $this->denyAccessUnlessGranted(RecetteVoter::VIEW, $objRecette);

        $objUser    = $this->getUser();
        $boolLiked  = false;
        $boolInList = false;
        $userNote   = null;

        if ($objUser) {
            $boolLiked  = (bool) $likeRepository->findOneBy(['likeUser' => $objUser, 'likeRecette' => $objRecette]);
            $boolInList = $objRecette->getListes()->exists(
                fn($k, $liste) => $liste->getUser() === $objUser
            );
            $noteEntity = $noteRepository->findOneBy(['user' => $objUser, 'recette' => $objRecette]);
            $userNote   = $noteEntity ? $noteEntity->getValeur() : null;
        }

        $commentaires = $commentaireRepository->findBy(
            ['recette' => $objRecette, 'isVisible' => true],
            ['createdAt' => 'DESC']
        );

        return $this->render('recette/show.html.twig', [
            'recette'      => $objRecette,
            'is_liked'     => $boolLiked,
            'is_in_list'   => $boolInList,
            'like_count'   => $likeRepository->count(['likeRecette' => $objRecette]),
            'user_note'    => $userNote,
            'avg_note'     => $noteRepository->getAverageForRecette($objRecette),
            'note_count'   => $noteRepository->count(['recette' => $objRecette]),
            'commentaires' => $commentaires,
            'comment_url'  => $this->generateUrl('app_commentaire_add', ['id' => $objRecette->getId()]),
            'note_url'     => $this->generateUrl('app_note_add', ['id' => $objRecette->getId()]),
        ]);
    }

    /**
     * Crée une nouvelle recette utilisateur. Téléverse la photo si elle est fournie.
     *
     * @param Request                $objRequest       Requête HTTP
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     * @param SluggerInterface       $objSlugger       Service de génération de nom de fichier sécurisé
     */
    #[Route('/recette/nouvelle', name: 'app_recette_new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request                $objRequest,
        EntityManagerInterface $objEntityManager,
        SluggerInterface       $objSlugger
    ): Response {
        $objRecette = new Recette();
        $objForm = $this->createForm(RecetteType::class, $objRecette);
        $objForm->handleRequest($objRequest);

        if ($objForm->isSubmitted() && $objForm->isValid()) {
            /** @var UploadedFile|null $objPhotoFile */
            $objPhotoFile = $objForm->get('recettePhotoFile')->getData();

            if ($objPhotoFile) {
                $strNomFichier = $this->uploadPhoto($objPhotoFile, $objSlugger);
                $objRecette->setRecettePhoto($strNomFichier);
            }

            $intNumero = 1;
            foreach ($objRecette->getEtapes() as $objEtape) {
                $objEtape->setEtapeNumero($intNumero++);
            }

            $objRecette->setRecetteStatut('prive');
            $objRecette->setCreatedBy($this->getUser());
            $objEntityManager->persist($objRecette);
            $objEntityManager->flush();

            $this->addFlash('success', 'Recette créée avec succès !');
            return $this->redirectToRoute('app_recette_show', ['id' => $objRecette->getId()]);
        }

        return $this->render('recette/new.html.twig', [
            'form' => $objForm->createView(),
        ]);
    }

    /**
     * Modifie une recette existante. Remplace la photo si une nouvelle est fournie.
     *
     * @param Recette                $objRecette       La recette à modifier
     * @param Request                $objRequest       Requête HTTP
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     * @param SluggerInterface       $objSlugger       Service de génération de nom de fichier sécurisé
     */
    #[Route('/recette/{id}/modifier', name: 'app_recette_edit', requirements: ['id' => '\d+'])]
    #[IsGranted(RecetteVoter::EDIT, subject: 'objRecette')]
    public function edit(
        Recette                $objRecette,
        Request                $objRequest,
        EntityManagerInterface $objEntityManager,
        SluggerInterface       $objSlugger
    ): Response {
        $objForm = $this->createForm(RecetteType::class, $objRecette);
        $objForm->handleRequest($objRequest);

        if ($objForm->isSubmitted() && $objForm->isValid()) {
            /** @var UploadedFile|null $objPhotoFile */
            $objPhotoFile = $objForm->get('recettePhotoFile')->getData();

            if ($objPhotoFile) {
                if ($objRecette->getRecettePhoto()) {
                    $strAnciennePhoto = $this->getParameter('photos_directory') . '/' . $objRecette->getRecettePhoto();
                    if (file_exists($strAnciennePhoto)) {
                        unlink($strAnciennePhoto);
                    }
                }

                $strNomFichier = $this->uploadPhoto($objPhotoFile, $objSlugger);
                $objRecette->setRecettePhoto($strNomFichier);
            }

            $objEntityManager->flush();

            $this->addFlash('success', 'Recette modifié avec succés !');
            return $this->redirectToRoute('app_recette_show', ['id' => $objRecette->getId()]);
        }

        return $this->render('recette/edit.html.twig', [
            'recette' => $objRecette,
            'form'    => $objForm,
        ]);
    }

    /**
     * Téléverse la photo d'une recette dans le répertoire configuré.
     *
     * @throws \RuntimeException Si le déplacement du fichier échoue
     */
    private function uploadPhoto(UploadedFile $objFile, SluggerInterface $objSlugger): string
    {
        $strNomOriginal = pathinfo($objFile->getClientOriginalName(), PATHINFO_FILENAME);
        $strNomSecurise = $objSlugger->slug($strNomOriginal);
        $strNomFichier  = $strNomSecurise . '-' . uniqid() . '.' . $objFile->guessExtension();

        try {
            $objFile->move(
                $this->getParameter('photos_directory'),
                $strNomFichier
            );
        } catch (FileException $e) {
            throw new \RuntimeException('Erreur lors du téléversement de la photo.');
        }

        return $strNomFichier;
    }

    /**
     * Supprime définitivement une recette après vérification du token CSRF.
     *
     * @param Recette                $objRecette       La recette à supprimer
     * @param Request                $objRequest       Requête HTTP (token CSRF)
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
    #[Route('/recette/{id}/supprimer', name: 'app_recette_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(RecetteVoter::DELETE, subject: 'objRecette')]
    public function delete(
        Recette                $objRecette,
        Request                $objRequest,
        EntityManagerInterface $objEntityManager
    ): Response {
        if (!$this->isCsrfTokenValid('delete_recette_' . $objRecette->getId(), $objRequest->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_recette_index');
        }

        $objEntityManager->remove($objRecette);
        $objEntityManager->flush();

        $this->addFlash('success', 'Recette supprimé.');
        return $this->redirectToRoute('app_search');
    }
}