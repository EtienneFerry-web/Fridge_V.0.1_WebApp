<?php

namespace App\Tests\Application\Controller;

use App\Factory\UserFactory;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Zenstruck\Foundry\Attribute\ResetDatabase;

#[ResetDatabase]
class AuthControllerTest extends WebTestCase
{
    public function testLoginPageShow(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        
        $this->assertResponseIsSuccessful();
    }
    
    public function testLoginSuccessWithCorrectCredentials(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        
        $this->assertResponseIsSuccessful();
        
        $objUser = UserFactory::createOne();

        $client->submitForm('Se connecter', [
            'email'    => $objUser->getStrEmail(),
            'password' => UserFactory::DEFAULT_PASSWORD
        ]);

        $this->assertResponseRedirects();
        $client->followRedirect();
        $this->assertRouteSame('app_home'); 
    }
    
    public function testLoginFailedWithBadPassword(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        
        $this->assertResponseIsSuccessful();

        $objUser = UserFactory::createOne();

        $client->submitForm('Se connecter', [
            'email'    => $objUser->getStrEmail(),
            'password' => 'BadPassword'
        ]);

        $this->assertResponseRedirects();  
        $client->followRedirect();
        $this->assertRouteSame('app_login');    
        $this->assertAnySelectorTextContains('div', 'Invalid credentials.');
    }
    
    public function testLoginFailedWithBadEmail(): void
    {
        $client = static::createClient();
        $client->request('GET', '/login');
        
        $this->assertResponseIsSuccessful();

        $client->submitForm('Se connecter', [
            'email'    => 'NotExist@mail.com',
            'password' => 'BadPassword'
        ]);

        $this->assertResponseRedirects();  
        $client->followRedirect();
        $this->assertRouteSame('app_login'); 
        $this->assertAnySelectorTextContains('div', 'Invalid credentials.');
    }
}