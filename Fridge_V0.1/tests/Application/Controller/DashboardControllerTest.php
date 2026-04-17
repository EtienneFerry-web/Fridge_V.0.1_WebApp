<?php
namespace App\Tests\Controller;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Test\ResetDatabase;
use Zenstruck\Foundry\Test\Factories;

class DashboardControllerTest extends WebTestCase
{
    use ResetDatabase;
    use Factories;

    public function testDashboardAccessDeniedForAnonymous(): void
    {
        $client = static::createClient();
        $client->request('GET', '/dashboard');
        $this->assertResponseRedirects();
    }

    public function testDashboardAccessDeniedForSimpleUser(): void
    {
        $client = static::createClient();
        $objUser = UserFactory::createOne();
        $client->loginUser($objUser);   //< plus de _real()
        $client->request('GET', '/dashboard');
        $this->assertResponseStatusCodeSame(403);
    }

    public function testDashboardShowForModerator(): void
    {
        $client = static::createClient();
        $objModerator = UserFactory::createOne([
            'roles' => ['ROLE_MODERATOR'],
        ]);
        $client->loginUser($objModerator);   //< plus de _real()
        $client->request('GET', '/dashboard');
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Tableau de bord');
    }

    public function testDashboardShowForAdmin(): void
    {
        $client = static::createClient();
        $objAdmin = UserFactory::createOne([
            'roles' => ['ROLE_ADMIN'],
        ]);
        $client->loginUser($objAdmin);   //< plus de _real()
        $client->request('GET', '/dashboard');
        $this->assertResponseIsSuccessful();
    }
}