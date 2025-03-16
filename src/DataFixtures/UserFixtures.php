<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        // Create admin user
        $adminUser = new User();
        $adminUser->setEmail('admin@example.com');
        $adminUser->setRoles(['ROLE_ADMIN']);
        $adminUser->setFirstName('Admin');
        $adminUser->setLastName('User');
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $adminUser,
            'admin123'
        );
        $adminUser->setPassword($hashedPassword);
        
        $manager->persist($adminUser);
        
        // Create regular user
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setRoles(['ROLE_USER']);
        
        $hashedPassword = $this->passwordHasher->hashPassword(
            $user,
            'user123'
        );
        $user->setPassword($hashedPassword);
        
        $manager->persist($user);
        
        $manager->flush();
    }
} 