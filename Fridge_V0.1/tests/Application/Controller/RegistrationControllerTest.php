<?php
namespace App\Tests\Application\Controller;

use App\Entity\User;
use App\Factory\UserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\Factories;
use Zenstruck\Foundry\Test\ResetDatabase;

class RegistrationControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    // ========== AFFICHAGE DE LA PAGE ==========

    public function testRegisterPageShow(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Inscription');
        $this->assertSelectorExists('form');
    }

    // ========== SOUMISSION VALIDE ==========

    public function testRegisterFormSubmitSuccess(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Créer mon compte', [
            'registration_form[strName]'       => 'Dupont',
            'registration_form[strFirstname]'  => 'Marie',
            'registration_form[strUsername]'   => 'marie_cuisine',
            'registration_form[strEmail]'      => 'marie@example.com',
            'registration_form[plainPassword]' => 'P@ssw0rd123',
        ]);

        // Après succès, on est redirigé vers app_home
        $this->assertResponseRedirects('/');

        // On vérifie que l'utilisateur est bien en base
        $objEntityManager = static::getContainer()->get(EntityManagerInterface::class);
        $objUser = $objEntityManager->getRepository(User::class)
            ->findOneBy(['strEmail' => 'marie@example.com']);
        $this->assertNotNull($objUser);
        $this->assertSame('Dupont', $objUser->getStrName());
        $this->assertSame('Marie', $objUser->getStrFirstname());
        $this->assertSame('marie_cuisine', $objUser->getStrUsername());
        // Le mot de passe doit être hashé, pas en clair
        $this->assertNotSame('P@ssw0rd123', $objUser->getPassword());
    }

    // ========== VALIDATION - MOT DE PASSE TROP COURT ==========

    public function testRegisterWithTooShortPassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Créer mon compte', [
            'registration_form[strName]'       => 'Dupont',
            'registration_form[strFirstname]'  => 'Marie',
            'registration_form[strUsername]'   => 'marie_cuisine',
            'registration_form[strEmail]'      => 'marie@example.com',
            'registration_form[plainPassword]' => 'abc', // < 6 caractères
        ]);

        // Symfony 7+ : form invalide = code 422 (Unprocessable Content)
        $this->assertResponseStatusCodeSame(422);
        // Le message d'erreur doit être affiché dans le HTML
        $this->assertSelectorTextContains('body', 'at least 6 characters');
        // Aucun utilisateur ne doit être créé
        $objEntityManager = static::getContainer()->get(EntityManagerInterface::class);
        $objUser = $objEntityManager->getRepository(User::class)
            ->findOneBy(['strEmail' => 'marie@example.com']);
        $this->assertNull($objUser);
    }

    // ========== VALIDATION - MOT DE PASSE VIDE ==========

    public function testRegisterWithEmptyPassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/register');

        $client->submitForm('Créer mon compte', [
            'registration_form[strName]'       => 'Dupont',
            'registration_form[strFirstname]'  => 'Marie',
            'registration_form[strUsername]'   => 'marie_cuisine',
            'registration_form[strEmail]'      => 'marie@example.com',
            'registration_form[plainPassword]' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'Please enter a password');
    }

    // ========== VALIDATION - EMAIL DÉJÀ EXISTANT ==========

    public function testRegisterWithExistingEmail(): void
    {
        $client = static::createClient();
        // Un utilisateur existe déjà avec cet email
        UserFactory::createOne(['strEmail' => 'dejapris@example.com']);

        $client->request('GET', '/register');
        $client->submitForm('Créer mon compte', [
            'registration_form[strName]'       => 'Dupont',
            'registration_form[strFirstname]'  => 'Marie',
            'registration_form[strUsername]'   => 'marie_cuisine',
            'registration_form[strEmail]'      => 'dejapris@example.com',
            'registration_form[plainPassword]' => 'P@ssw0rd123',
        ]);

        // Le form est ré-affiché avec l'erreur (422)
        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'There is already an account with this email');
    }
}