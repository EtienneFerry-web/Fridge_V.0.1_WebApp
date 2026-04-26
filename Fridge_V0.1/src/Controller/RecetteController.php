<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use App\Security\Voter\RecetteVoter;
use App\Service\RecetteImporter;
use App\Service\SpoonacularClient;
use App\Service\SpoonacularMapper;
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
 * Gère deux mondes :
 * - Recettes utilisateur (BDD, source='user', statut='prive') : CRUD complet, visibles uniquement par leur créateur
 * - Recettes Spoonacular (API directe, ou BDD si sauvegardées) : consultation publique, import au clic "Sauvegarder"
 */
final class RecetteController extends AbstractController
{
    /**
     * Liste de découverte : recettes Spoonacular en API directe avec filtres et tri.
     *
     * Aucune lecture BDD ici — la liste publique est entièrement servie par Spoonacular.
     * Les recettes ne sont importées en BDD qu'au clic "Sauvegarder" (cf. spoonacularSave).
     *
     * @param Request           $objRequest Requête HTTP (paramètres ?regime= et ?sort=)
     * @param SpoonacularClient $objClient  Client API Spoonacular
     * @param SpoonacularMapper $objMapper  Mapper de régimes vers paramètres API
     */
    #[Route('/recette', name: 'app_recette_index')]
    public function index(
        Request           $objRequest,
        SpoonacularClient $objClient,
        SpoonacularMapper $objMapper
    ): Response {
        $strRegime = $objRequest->query->get('regime', 'all');
        $strSort   = $objRequest->query->get('sort', 'recent');

        // Mapping du tri interne vers le tri Spoonacular
        $strApiSort = match ($strSort) {
            'popular' => 'popularity',
            'recent'  => 'popularity', // pas d'équivalent natif côté API
            default   => 'popularity',
        };

        // Mapping du régime interne vers les paramètres API (diet ou intolerances)
        $arrFilters = $strRegime !== 'all'
            ? $objMapper->mapRegimeToApiParams($strRegime)
            : [];

        $arrRecettes = [];

        try {
            $arrResponse = $objClient->complexSearch(
                intNumber:  24,
                intOffset:  0,
                strSort:    $strApiSort,
                arrFilters: $arrFilters
            );
            $arrRecettes = $arrResponse['results'] ?? [];
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de charger les recettes pour le moment. Réessaie dans un instant.');
        }

        return $this->render('recette/index.html.twig', [
            'recettes'     => $arrRecettes,
            'activeRegime' => $strRegime,
            'activeSort'   => $strSort,
            'isApiList'    => true, // flag pour le template : on est en mode Spoonacular brut
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
     * Affiche le détail d'une recette Spoonacular SANS l'importer en BDD.
     *
     * Utilise l'API Spoonacular en lecture seule. Si l'utilisateur veut conserver
     * la recette pour la mettre en favoris/listes, il clique sur "Sauvegarder"
     * et la route spoonacularSave déclenche l'import.
     *
     * IMPORTANT : cette route doit être déclarée AVANT show() pour éviter tout
     * conflit de matching avec /recette/{id}.
     *
     * @param int               $spoonacularId ID Spoonacular de la recette
     * @param SpoonacularClient $objClient     Client API Spoonacular
     */
    #[Route(
        '/recette/spoonacular/{spoonacularId}',
        name: 'app_recette_spoonacular_show',
        requirements: ['spoonacularId' => '\d+']
    )]
    public function spoonacularShow(
        int               $spoonacularId,
        SpoonacularClient $objClient
    ): Response {
        try {
            $arrData = $objClient->getRecipeInformation($spoonacularId);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible de charger cette recette pour le moment.');
            return $this->redirectToRoute('app_recette_index');
        }

        return $this->render('recette/spoonacular_show.html.twig', [
            'data' => $arrData,
        ]);
    }

    /**
     * Importe une recette Spoonacular en BDD au clic "Sauvegarder", puis redirige
     * vers la page détail classique.
     *
     * Si la recette est déjà importée (anti-doublon via spoonacularId), on récupère
     * simplement l'existante au lieu de dupliquer.
     *
     * @param int             $spoonacularId ID Spoonacular de la recette
     * @param Request         $objRequest    Requête HTTP (token CSRF)
     * @param RecetteImporter $objImporter   Service d'import
     */
    #[Route(
        '/recette/spoonacular/{spoonacularId}/save',
        name: 'app_recette_spoonacular_save',
        methods: ['POST'],
        requirements: ['spoonacularId' => '\d+']
    )]
    #[IsGranted('ROLE_USER')]
    public function spoonacularSave(
        int             $spoonacularId,
        Request         $objRequest,
        RecetteImporter $objImporter
    ): Response {
        if (!$this->isCsrfTokenValid('save_spoonacular_' . $spoonacularId, $objRequest->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_recette_spoonacular_show', ['spoonacularId' => $spoonacularId]);
        }

        try {
            $objRecette = $objImporter->importFromSpoonacular($spoonacularId);
        } catch (\Throwable $e) {
            $this->addFlash('error', 'Impossible d\'importer cette recette : ' . $e->getMessage());
            return $this->redirectToRoute('app_recette_spoonacular_show', ['spoonacularId' => $spoonacularId]);
        }

        $this->addFlash('success', 'Recette sauvegardée dans votre bibliothèque !');
        return $this->redirectToRoute('app_recette_show', ['id' => $objRecette->getId()]);
    }

    /**
     * Affiche le détail d'une recette en BDD (user ou Spoonacular sauvegardée).
     *
     * @param Recette $objRecette La recette à afficher (résolue automatiquement)
     */
    #[Route('/recette/{id}', name: 'app_recette_show', requirements: ['id' => '\d+'])]
    public function show(Recette $objRecette): Response
    {
        $this->denyAccessUnlessGranted(RecetteVoter::VIEW, $objRecette);

        return $this->render('recette/show.html.twig', [
            'recette' => $objRecette,
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