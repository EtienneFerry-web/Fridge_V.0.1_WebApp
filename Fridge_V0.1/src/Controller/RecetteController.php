<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Security\Voter\RecetteVoter;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Service\SpoonacularClient;
use App\Service\SpoonacularMapper;

/**
 * Contrôleur CRUD des recettes.
 *
 * Gère la liste, l'affichage, la création, la modification et la suppression des recettes,
 * incluant le téléversement de la photo associée.
 */
final class RecetteController extends AbstractController
{
    /**
     * Liste toutes les recettes publiées avec filtres de régime et tri.
     *
     * @param RecetteRepository $objRepository Repository des recettes
     * @param Request           $objRequest    Requête HTTP (paramètres ?regime= et ?sort=)
     */
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
        Request $objRequest,
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
        $strFlashError = null;

        try {
            $arrResponse = $objClient->complexSearch(
                intNumber:  24,
                intOffset:  0,
                strSort:    $strApiSort,
                arrFilters: $arrFilters
            );
            $arrRecettes = $arrResponse['results'] ?? [];
        } catch (\Throwable $e) {
            // En cas d'erreur API (quota, réseau, etc.) on dégrade proprement
            $strFlashError = 'Impossible de charger les recettes pour le moment. Réessaie dans un instant.';
        }

        if ($strFlashError !== null) {
            $this->addFlash('error', $strFlashError);
        }

        return $this->render('recette/index.html.twig', [
            'recettes'     => $arrRecettes,
            'activeRegime' => $strRegime,
            'activeSort'   => $strSort,
            'isApiList'    => true, // flag pour le template : on est en mode Spoonacular brut
        ]);
    }

    /**
     * Affiche le détail d'une recette.
     *
     * @param Recette $objRecette La recette à afficher (résolu automatiquement par le ParamConverter)
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
     * Crée une nouvelle recette. Téléverse la photo si elle est fournie.
     *
     * La recette est enregistrée puis l'utilisateur est redirigé vers sa page de détail.
     *
     * @param Request                $objRequest       Requête HTTP
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     * @param SluggerInterface       $objSlugger       Service de génération de nom de fichier sécurisé
     */
    #[Route('/recette/nouvelle', name: 'app_recette_new')]
    #[IsGranted('ROLE_USER')]
    public function new(
        Request $objRequest, 
        EntityManagerInterface $objEntityManager,
        SluggerInterface $objSlugger
    ): Response{
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
     * Modifie une recette existante. Remplace la photo si une nouvelle est fournie (supprime l'ancienne).
     *
     * L'accès est contrôlé par le RecetteVoter (auteur ou administrateur uniquement).
     *
     * @param Recette                $objRecette       La recette à modifier
     * @param Request                $objRequest       Requête HTTP
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     * @param SluggerInterface       $objSlugger       Service de génération de nom de fichier sécurisé
     */
    #[Route('/recette/{id}/modifier', name: 'app_recette_edit', requirements: ['id' => '\d+'])]
    #[IsGranted(RecetteVoter::EDIT, subject: 'objRecette')]
    public function edit(
        Recette $objRecette,
        Request $objRequest,
        EntityManagerInterface $objEntityManager,
        SluggerInterface $objSlugger
    ): Response {
        $objForm = $this->createForm(RecetteType::class, $objRecette);
        $objForm->handleRequest($objRequest);

        if($objForm->isSubmitted() && $objForm->isValid()){
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
            'recette'   => $objRecette,
            'form'      => $objForm,
        ]);
    }

    /**
     * Téléverse la photo d'une recette dans le répertoire configuré et retourne le nom du fichier généré.
     *
     * @param UploadedFile     $objFile    Fichier image uploadé
     * @param SluggerInterface $objSlugger Service de slugification du nom de fichier
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
     * L'accès est contrôlé par le RecetteVoter (auteur ou administrateur uniquement).
     *
     * @param Recette                $objRecette       La recette à supprimer
     * @param Request                $objRequest       Requête HTTP (contient le token CSRF)
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
    #[Route('/recette/{id}/supprimer', name: 'app_recette_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(RecetteVoter::DELETE, subject: 'objRecette')]
    public function delete(
        Recette $objRecette,
        Request $objRequest,
        EntityManagerInterface $objEntityManager
    ): Response {
        if(!$this->isCsrfTokenValid('delete_recette_' . $objRecette->getId(), $objRequest->request->get('_token'))) {
            $this->addFlash('error', 'Token invalide.');
            return $this->redirectToRoute('app_recette_index');
        }

        $objEntityManager->remove($objRecette);
        $objEntityManager->flush();

        $this->addFlash('success', 'Recette supprimé.');
        return $this->redirectToRoute('app_search');
    }
}
