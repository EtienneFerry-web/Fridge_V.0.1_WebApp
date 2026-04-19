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

/**
 * Contrôleur de gestion du stock d'ingrédients de l'utilisateur.
 *
 * Permet à l'utilisateur connecté de consulter, ajouter, modifier, supprimer et vider son stock personnel.
 */
#[Route('/stock')]
final class StockController extends AbstractController
{
    /**
     * Affiche le stock de l'utilisateur connecté, trié par id décroissant.
     *
     * @param StockerRepository $objStockerRepository Repository des entrées de stock
     */
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

    /**
     * Ajoute un ingrédient au stock de l'utilisateur.
     *
     * Crée l'ingrédient en base s'il n'existe pas encore. Le nom est obligatoire.
     *
     * @param Request                $request               Requête HTTP (nom, quantité, unité, seuil, date de péremption)
     * @param IngredientRepository   $objIngredientRepository Repository des ingrédients
     * @param EntityManagerInterface $objEntityManager      Gestionnaire d'entités Doctrine
     */
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

    /**
     * Modifie la quantité, l'unité, le seuil et la date de péremption d'une entrée de stock.
     *
     * Vérifie que l'entrée appartient bien à l'utilisateur connecté.
     *
     * @param Stocker                $objStocker       L'entrée de stock à modifier
     * @param Request                $request          Requête HTTP (quantité, unité, seuil, date de péremption)
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
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

    /**
     * Supprime une entrée du stock appartenant à l'utilisateur connecté.
     *
     * @param Stocker                $objStocker       L'entrée de stock à supprimer
     * @param EntityManagerInterface $objEntityManager Gestionnaire d'entités Doctrine
     */
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

    /**
     * Vide entièrement le stock de l'utilisateur connecté.
     *
     * @param StockerRepository      $objStockerRepository Repository des entrées de stock
     * @param EntityManagerInterface $objEntityManager     Gestionnaire d'entités Doctrine
     */
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
