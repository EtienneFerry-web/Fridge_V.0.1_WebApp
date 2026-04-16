<?php

namespace App\Factory;

use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<User>
 */
final class UserFactory extends PersistentObjectFactory
{
    public const DEFAULT_PASSWORD = 'P@ssw0rd';

    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher
    ) {}

    public static function class(): string
    {
        return User::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'strEmail'     => self::faker()->email(),
            'strName'      => self::faker()->lastName(),
            'strFirstname' => self::faker()->firstName(),
            'strUsername'  => self::faker()->userName(),
            'password'     => $this->userPasswordHasher->hashPassword(new User(), self::DEFAULT_PASSWORD),
            'isVerified'   => true,
        ];
    }

    protected function initialize(): static
    {
        return $this;
    }
}