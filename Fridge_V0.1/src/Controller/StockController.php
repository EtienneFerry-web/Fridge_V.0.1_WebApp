<?php

namespace App\Controller;

use App\Entity\Ingredient;
use App\Entity\Stocker;
use App\Repository\IngredientRepository;
use App\Repository\StockerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/stock')]
final class StockController extends AbstractController
{
    #[Route('', name: 'app_stock')]
    public function index(StockerRepository $objStockerRepository): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $arrStock = $objStockerRepository->findBy(
            ['user' => $this->getUser()],
            ['id' => 'DESC']
        );

        return $this->render('stock/index.html.twig', [
            'arrStock' => $arrStock,
        ]);
    }

    #[Route('/ajouter', name: 'app_stock_add', methods: ['POST'])]
    public function add(
        Request                $request,
        IngredientRepository   $objIngredientRepository,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $strNom    = trim($request->request->get('ingredientName', ''));
        $fltQty    = (float) $request->request->get('ingredientQty', 0);
        $strUnite  = $request->request->get('ingredientUnit', 'unité');
        $fltSeuil  = (float) $request->request->get('ingredientThreshold', 0);
        $strExpiry = $request->request->get('ingredientExpiry', '');

        if (!$strNom) {
            $this->addFlash('error', 'Le nom est obligatoire.');
            return $this->redirectToRoute('app_stock');
        }

        $objIngredient = $objIngredientRepository->findOneBy(['ingredientLibelle' => $strNom]);
        if (!$objIngredient) {
            $objIngredient = new Ingredient();
            $objIngredient->setIngredientLibelle($strNom);
            $objEntityManager->persist($objIngredient);
        }

        $objStocker = new Stocker();
        $objStocker->setUser($this->getUser())
                   ->setIngredient($objIngredient)
                   ->setStockerQuantiteDispo((string) $fltQty)
                   ->setStockerUnite($strUnite)
                   ->setStockerSeuil($fltSeuil > 0 ? (string) $fltSeuil : null);

        if ($strExpiry) {
            $objStocker->setStockerDatePeremption(
                \DateTimeImmutable::createFromFormat('Y-m-d', $strExpiry)
            );
        }

        $objEntityManager->persist($objStocker);
        $objEntityManager->flush();

        $this->addFlash('success', 'Ingrédient ajouté au stock.');
        return $this->redirectToRoute('app_stock');
    }

    #[Route('/modifier/{id}', name: 'app_stock_edit', methods: ['POST'])]
    public function edit(
        Stocker                $objStocker,
        Request                $request,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($objStocker->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $fltQty    = (float) $request->request->get('ingredientQty', 0);
        $strUnite  = $request->request->get('ingredientUnit', 'unité');
        $fltSeuil  = (float) $request->request->get('ingredientThreshold', 0);
        $strExpiry = $request->request->get('ingredientExpiry', '');

        $objStocker->setStockerQuantiteDispo((string) $fltQty)
                   ->setStockerUnite($strUnite)
                   ->setStockerSeuil($fltSeuil > 0 ? (string) $fltSeuil : null)
                   ->setStockerDatePeremption(
                       $strExpiry
                           ? \DateTimeImmutable::createFromFormat('Y-m-d', $strExpiry)
                           : null
                   );

        $objEntityManager->flush();

        $this->addFlash('success', 'Ingrédient modifié.');
        return $this->redirectToRoute('app_stock');
    }

    #[Route('/supprimer/{id}', name: 'app_stock_delete', methods: ['POST'])]
    public function delete(
        Stocker                $objStocker,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        if ($objStocker->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException();
        }

        $objEntityManager->remove($objStocker);
        $objEntityManager->flush();

        $this->addFlash('success', 'Ingrédient supprimé.');
        return $this->redirectToRoute('app_stock');
    }

    #[Route('/vider', name: 'app_stock_clear', methods: ['POST'])]
    public function clear(
        StockerRepository      $objStockerRepository,
        EntityManagerInterface $objEntityManager
    ): Response {
        $this->denyAccessUnlessGranted('ROLE_USER');

        foreach ($objStockerRepository->findBy(['user' => $this->getUser()]) as $objStocker) {
            $objEntityManager->remove($objStocker);
        }

        $objEntityManager->flush();

        $this->addFlash('success', 'Stock vidé.');
        return $this->redirectToRoute('app_stock');
    }
}
