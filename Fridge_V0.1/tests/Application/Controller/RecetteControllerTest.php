<?php
namespace App\Tests\Application\Controller;

use App\Factory\RecetteFactory;
use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RecetteControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    // ========== LISTE DES RECETTES ==========

    public function testIndexPageShowEmpty(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recette');
        $this->assertResponseIsSuccessful();
        // À adapter au <h1> réel de ton template recette/index.html.twig
        $this->assertSelectorExists('h1');
    }

    public function testIndexPageShowOneRecette(): void
    {
        $client = static::createClient();
        RecetteFactory::createOne(['recetteLibelle' => 'Tarte aux pommes']);
        $client->request('GET', '/recette');
        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('body', 'Tarte aux pommes');
    }

    public function testIndexPageShowManyRecettes(): void
    {
        $client = static::createClient();
        RecetteFactory::createMany(10);
        $client->request('GET', '/recette');
        $this->assertResponseIsSuccessful();
    }

    // ========== PAGE DE CRÉATION ==========

    public function testCreatePageRedirectsIfNotLoggedIn(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recette/nouvelle');
        // ROLE_USER requis -> anonyme redirigé vers le login
        $this->assertResponseRedirects();
    }

    public function testCreatePageShowWhenLoggedIn(): void
    {
        $client = static::createClient();
        $objUser = UserFactory::createOne();
        $client->loginUser($objUser);
        $client->request('GET', '/recette/nouvelle');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');
    }

    // ========== SOUMISSION DU FORMULAIRE ==========

    public function testCreateRecetteFormSubmit(): void
    {
        $client = static::createClient();
        $objUser = UserFactory::createOne();
        $client->loginUser($objUser);

        $client->request('GET', '/recette/nouvelle');
        $this->assertResponseIsSuccessful();

        // ⚠️ Remplace "Enregistrer" par le texte EXACT du bouton submit de ton template
        $client->submitForm('Enregistrer', [
            'recette[recetteLibelle]'       => 'Tarte aux pommes test',
            'recette[recetteDescription]'   => 'Une délicieuse tarte maison',
            'recette[recetteDifficulte]'    => 'Facile',
            'recette[recettePortion]'       => 4,
            'recette[recetteTempsPrepa]'    => 30,
            'recette[recetteTempsCuisson]'  => 45,
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_recette_show');
    }

    // ========== DÉTAIL D'UNE RECETTE ==========

    public function testShowRecette(): void
    {
        $client = static::createClient();
        $objRecette = RecetteFactory::createOne(['recetteLibelle' => 'Ma super recette']);
        $client->request('GET', '/recette/' . $objRecette->getId());
        $this->assertResponseIsSuccessful();
        $this->assertAnySelectorTextContains('body', 'Ma super recette');
    }
}
