<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class SecurityControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private string $loginPath = '/login';
    private string $logoutPath = '/logout';
    private string $homePath = '/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get('security.user_password_hasher');
        
        // Nettoyer la base de données de test avant chaque test
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
    }

    public function testLoginPageIsAccessible(): void
    {
        $this->client->request('GET', $this->loginPath);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
    }

    public function testLoginWithValidCredentials(): void
    {
        // Créer un utilisateur de test
        $user = $this->createTestUser('test@example.com', 'Password123!');
        
        $crawler = $this->client->request('GET', $this->loginPath);
        
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $this->client->submit($form);
        
        // Vérifier la redirection après connexion réussie
        $this->assertResponseRedirects('/dashboard');
        
        // Suivre la redirection
        $this->client->followRedirect();
        
        // Vérifier que l'utilisateur est bien connecté
        $this->assertSelectorTextContains('h1', 'Bienvenue sur PoolManager');
    }

    public function testLoginWithInvalidCredentials(): void
    {
        // Créer un utilisateur de test
        $this->createTestUser('test@example.com', 'Password123!');
        
        $crawler = $this->client->request('GET', $this->loginPath);
        
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $this->client->submit($form);
        
        // Vérifier la redirection vers la page de connexion
        $this->assertResponseRedirects($this->loginPath);
        
        // Suivre la redirection
        $this->client->followRedirect();
        
        // Vérifier qu'il y a un message d'erreur (div avec bg-red-100)
        $this->assertSelectorExists('.bg-red-100');
    }

    public function testLoginWithNonexistentUser(): void
    {
        $crawler = $this->client->request('GET', $this->loginPath);
        
        $form = $crawler->selectButton('Se connecter')->form([
            'email' => 'nonexistent@example.com',
            'password' => 'somepassword',
        ]);

        $this->client->submit($form);
        
        // Vérifier la redirection vers la page de connexion
        $this->assertResponseRedirects($this->loginPath);
        
        // Suivre la redirection
        $this->client->followRedirect();
        
        // Vérifier qu'il y a un message d'erreur (div avec bg-red-100)
        $this->assertSelectorExists('.bg-red-100');
    }

    public function testLogout(): void
    {
        // Créer et connecter un utilisateur
        $user = $this->createTestUser('test@example.com', 'Password123!');
        $this->client->loginUser($user);
        
        // Vérifier que l'utilisateur est bien connecté
        $this->assertNotNull($this->client->getContainer()->get('security.token_storage')->getToken());
        
        // Se déconnecter
        $this->client->request('GET', $this->logoutPath);
        
        // Vérifier la redirection après déconnexion
        $this->assertResponseRedirects('/login');
        
        // Suivre la redirection
        $this->client->followRedirect();
        
        // Vérifier que l'utilisateur est bien déconnecté
        $this->assertNull($this->client->getContainer()->get('security.token_storage')->getToken());
    }

    public function testRedirectIfAlreadyLoggedIn(): void
    {
        // Créer et connecter un utilisateur
        $user = $this->createTestUser('test@example.com', 'Password123!');
        $this->client->loginUser($user);
        
        // Essayer d'accéder à la page de connexion
        $this->client->request('GET', $this->loginPath);
        
        // Vérifier la redirection vers la page d'accueil
        $this->assertResponseRedirects('/');
    }

    /**
     * Crée un utilisateur de test en base de données
     */
    private function createTestUser(string $email, string $password): User
    {
        $user = new User();
        $user->setEmail($email);
        $user->setNom('Test');
        $user->setPrenom('User');
        $user->setTelGsm('0612345678');
        $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        $user->setRoles(['ROLE_USER']);
        
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        
        return $user;
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Fermer la connexion à la base de données
        $this->entityManager->close();
    }
}
