<?php

namespace App\Tests;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

abstract class AuthenticatedWebTestCase extends WebTestCase
{
    protected KernelBrowser $client;
    protected User $authenticatedUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->client = static::createClient();
        
        // Créer un utilisateur de test et l'authentifier automatiquement
        $this->authenticatedUser = $this->createAndLoginUser();
    }

    private function createAndLoginUser(): User
    {
        $entityManager = static::getContainer()->get('doctrine')->getManager();
        $passwordHasher = static::getContainer()->get('security.user_password_hasher');

        // Nettoyer la base de données de test avant de créer l'utilisateur
        $entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();

        $user = new User();
        $user->setEmail('test@example.com');
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setTelGsm('0612345678');
        $user->setPassword($passwordHasher->hashPassword($user, 'Password123!'));
        $user->setRoles(['ROLE_ADMIN']); // ROLE_ADMIN pour accéder à toutes les routes

        $entityManager->persist($user);
        $entityManager->flush();

        // Authentifier l'utilisateur
        $this->client->loginUser($user);

        return $user;
    }
}

