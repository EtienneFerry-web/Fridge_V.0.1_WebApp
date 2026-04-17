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

final class RecetteController extends AbstractController
{
    // READ - Liste toutes les recettes
    #[Route('/recette', name: 'app_recette_index')]
    public function index(RecetteRepository $objRepository, Request $objRequest): Response
    {
        $strRegime = $objRequest->query->get('regime', 'all');
        $strSort   = $objRequest->query->get('sort', 'recent');

        $arrRecettes = $objRepository->findWithFilters($strRegime, $strSort);

        return $this->render('recette/index.html.twig', [
            'recettes'      => $arrRecettes,
            'activeRegime'  => $strRegime,
            'activeSort'    => $strSort,
        ]);
    }

    //READ - Détail d'une recette
    #[Route('/recette/{id}', name: 'app_recette_show', requirements: ['id' => '\d+'])]
    public function show(Recette $objRecette): Response
    {
        return $this->render('recette/show.html.twig', [
            'recette' => $objRecette,
        ]);
    }

    // CREATE - Crée une nouvelle recette
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

            $objEntityManager->persist($objRecette);
            $objEntityManager->flush();

            $this->addFlash('success', 'Recette créée avec succès !');
            return $this->redirectToRoute('app_recette_show', ['id' => $objRecette->getId()]);
        }

        return $this->render('recette/new.html.twig', [
            'form' => $objForm->createView(),
        ]);
    }

    // UPDATE - Modifie une recette existante
    #[Route('/recette/{id}/modifier', name: 'app_recette_edit', requirements: ['id' => '\d+'])]
    #[IsGranted(RecetteVoter::EDIT, subject: 'recette')]
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
                // Optionnel : supprimer l'ancienne photo
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

    // DELETE 

    #[Route('/recette/{id}/supprimer', name: 'app_recette_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted(RecetteVoter::DELETE, subject: 'recette')]
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
