<?php

namespace App\Controller;

use App\Entity\Recette;
use App\Form\RecetteType;
use App\Repository\RecetteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class RecetteController extends AbstractController
{
    // READ - Liste toutes les recettes
    #[Route('/recette', name: 'app_recette_')]
    public function index(RecetteRepository $objRepository): Response
    {
        $arrRecettes = $objRepository->findAll();
        return $this->render('recette/index.html.twig', [
            'recettes' => $arrRecettes,
        ]);
    }

    //READ - Détail d'une recette
    #[Route('/recette/{id}', name: 'app_recette_show')]
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
        EntityManagerInterface $objEntityManager
    ): Response{
        $objRecette = new Recette();
        $objForm = $this->createForm(RecetteType::class, $objRecette);
        $objForm->handleRequest($objRequest);

        if ($objForm->isSubmitted() && $objForm->isValid()) {
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
    #[IsGranted('ROLE_USER')]
    public function edit(
        Recette $objRecette,
        Request $objRequest,
        EntityManagerInterface $objEntityManager
    ): Response {
        $objForm = $this->createForm(RecetteType::class, $objRecette);
        $objForm->handleRequest($objRequest);

        if($objForm->isSubmitted() && $objForm->isValid()){
            $objEntityManager->flush();

            $this->addFlash('success', 'Recette modifié avec succés !');
            return $this->redirectToRoute('app_recette_show', ['id' => $objRecette->getId()]);
        }

        return $this->render('recette/edit.html.twig', [
            'recette'   => $objRecette,
            'form'      => $objForm,
        ]);
    }

    // DELETE 

    #[Route('/{id}/supprimer', name: 'app_recette_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    #[IsGranted('ROLE_USER')]
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
        return $this->redirectToRoute('app_recette_index');
    }
}
