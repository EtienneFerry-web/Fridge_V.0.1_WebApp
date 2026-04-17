<?php
namespace App\Tests\Application\Controller;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class SecurityControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    // ========== AFFICHAGE DE LA PAGE LOGIN ==========

    public function testLoginPageShow(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
        $this->assertSelectorExists('form');
        // On vérifie que les champs attendus existent bien
        $this->assertSelectorExists('input[name="email"]');
        $this->assertSelectorExists('input[name="password"]');
    }

    // ========== CONNEXION VALIDE ==========

    public function testLoginWithValidCredentials(): void
    {
        $client = static::createClient();
        // On crée un user avec un email connu et le mot de passe par défaut de la factory
        UserFactory::createOne([
            'strEmail' => 'user@example.com',
        ]);

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            'email'    => 'user@example.com',
            'password' => UserFactory::DEFAULT_PASSWORD, // 'P@ssw0rd'
        ]);

        // Après succès, Symfony redirige (vers la page cible ou la home)
        $this->assertResponseRedirects();
    }

    // ========== MAUVAIS MOT DE PASSE ==========

    public function testLoginWithInvalidPassword(): void
    {
        $client = static::createClient();
        UserFactory::createOne([
            'strEmail' => 'user@example.com',
        ]);

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            'email'    => 'user@example.com',
            'password' => 'mauvais_mot_de_passe',
        ]);

        // Symfony redirige vers /login en cas d'échec
        $this->assertResponseRedirects('/login');
        // On suit la redirection pour vérifier l'erreur affichée
        $client->followRedirect();
        $this->assertSelectorTextContains('.alert-danger', 'Invalid credentials');
    }

    // ========== UTILISATEUR INCONNU ==========

    public function testLoginWithUnknownUser(): void
    {
        $client = static::createClient();
        // Aucun user créé

        $client->request('GET', '/login');
        $client->submitForm('Se connecter', [
            'email'    => 'inconnu@example.com',
            'password' => 'peu_importe',
        ]);

        $this->assertResponseRedirects('/login');
        $client->followRedirect();
        $this->assertSelectorExists('.alert-danger');
    }

    // ========== DÉCONNEXION ==========

    public function testLogout(): void
    {
        $client = static::createClient();
        $objUser = UserFactory::createOne();
        $client->loginUser($objUser);

        // On vérifie d'abord qu'on est bien connecté (accès à une page protégée)
        $client->request('GET', '/recette/nouvelle');
        $this->assertResponseIsSuccessful();

        // On se déconnecte
        $client->request('GET', '/logout');
        $this->assertResponseRedirects();

        // On re-tente d'accéder à la page protégée -> redirection vers login
        $client->request('GET', '/recette/nouvelle');
        $this->assertResponseRedirects();
    }

    // ========== ACCÈS À UNE PAGE PROTÉGÉE SANS ÊTRE CONNECTÉ ==========

    public function testProtectedPageRedirectsToLogin(): void
    {
        $client = static::createClient();
        $client->request('GET', '/recette/nouvelle');
        $this->assertResponseRedirects('/login');
    }
}