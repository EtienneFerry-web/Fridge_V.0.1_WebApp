<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture implements DependentFixtureInterface
{
    // Constantes de référence — utilisées par les autres fixtures
    const ADMIN     = 'user-admin';
    const MODERATOR = 'user-moderateur';
    const USER_ETIENNE = 'user-etienne';
    const USER_ALICE   = 'user-alice';
    const USER_BOB     = 'user-bob';

    public function __construct(
        private UserPasswordHasherInterface $objHasher
    ) {}

    public function load(ObjectManager $manager): void
    {
        // --- Admin ---
        $objAdmin = new User();
        $objAdmin->setStrName('Admin')
                 ->setStrFirstname('Super')
                 ->setStrUsername('admin_fridge')
                 ->setStrEmail('admin@fridge.fr')
                 ->setRoles(['ROLE_ADMIN'])
                 ->setPassword($this->objHasher->hashPassword($objAdmin, 'Admin1234!'))
                 ->setIsVerified(true)
                 ->addRegime($this->getReference(RegimeFixtures::OMNIVORE, \App\Entity\Regime::class));
        $manager->persist($objAdmin);
        $this->addReference(self::ADMIN, $objAdmin);

        // --- Modérateur ---
        $objModo = new User();
        $objModo->setStrName('Moderateur')
                ->setStrFirstname('Jean')
                ->setStrUsername('modo_fridge')
                ->setStrEmail('modo@fridge.fr')
                ->setRoles(['ROLE_MODERATOR'])
                ->setPassword($this->objHasher->hashPassword($objModo, 'Modo1234!'))
                ->setIsVerified(true)
                ->addRegime($this->getReference(RegimeFixtures::OMNIVORE, \App\Entity\Regime::class));
        $manager->persist($objModo);
        $this->addReference(self::MODERATOR, $objModo);

        // --- Etienne (user existant) ---
        $objEtienne = new User();
        $objEtienne->setStrName('Ferry')
                   ->setStrFirstname('Etienne')
                   ->setStrUsername('etienne_ferry')
                   ->setStrEmail('etienne@fridge.fr')
                   ->setRoles(['ROLE_USER'])
                   ->setPassword($this->objHasher->hashPassword($objEtienne, 'User1234!'))
                   ->setIsVerified(true)
                   ->addRegime($this->getReference(RegimeFixtures::VEGETARIEN, \App\Entity\Regime::class))
                   ->addRegime($this->getReference(RegimeFixtures::SANS_GLUTEN, \App\Entity\Regime::class));
        $manager->persist($objEtienne);
        $this->addReference(self::USER_ETIENNE, $objEtienne);

        // --- Alice ---
        $objAlice = new User();
        $objAlice->setStrName('Dupont')
                 ->setStrFirstname('Alice')
                 ->setStrUsername('alice_dupont')
                 ->setStrEmail('alice@fridge.fr')
                 ->setRoles(['ROLE_USER'])
                 ->setPassword($this->objHasher->hashPassword($objAlice, 'User1234!'))
                 ->setIsVerified(true)
                 ->addRegime($this->getReference(RegimeFixtures::VEGAN, \App\Entity\Regime::class));
        $manager->persist($objAlice);
        $this->addReference(self::USER_ALICE, $objAlice);

        // --- Bob ---
        $objBob = new User();
        $objBob->setStrName('Martin')
               ->setStrFirstname('Bob')
               ->setStrUsername('bob_martin')
               ->setStrEmail('bob@fridge.fr')
               ->setRoles(['ROLE_USER'])
               ->setPassword($this->objHasher->hashPassword($objBob, 'User1234!'))
               ->setIsVerified(true)
               ->addRegime($this->getReference(RegimeFixtures::OMNIVORE, \App\Entity\Regime::class));
        $manager->persist($objBob);
        $this->addReference(self::USER_BOB, $objBob);

        $manager->flush();
    }

    // Symfony chargera RegimeFixtures AVANT UserFixtures
    public function getDependencies(): array
    {
        return [RegimeFixtures::class];
    }
}