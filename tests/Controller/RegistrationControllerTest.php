<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class RegistrationControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private UserPasswordHasherInterface $passwordHasher;
    private string $path = '/register';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->passwordHasher = $this->client->getContainer()->get('security.user_password_hasher');
        
        // Nettoyer la base de données de test avant chaque test
        $this->entityManager->createQuery('DELETE FROM App\Entity\User u')->execute();
        
        // Créer et authentifier un admin pour accéder à la page de registration
        // (car /register nécessite ROLE_ADMIN dans security.yaml)
        $admin = $this->createAdminUser();
        $this->client->loginUser($admin);
    }
    
    private function createAdminUser(): User
    {
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setNom('Admin');
        $admin->setPrenom('User');
        $admin->setTelGsm('0612345678');
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'AdminPassword123!'));
        $admin->setRoles(['ROLE_ADMIN']);
        
        $this->entityManager->persist($admin);
        $this->entityManager->flush();
        
        return $admin;
    }

    public function testRegisterPageIsAccessible(): void
    {
        $this->client->request('GET', $this->path);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un compte');
    }

    public function testRegisterWithValidData(): void
    {
        $crawler = $this->client->request('GET', $this->path);
        
        $form = $crawler->selectButton('S\'inscrire')->form([
            'registration_form[nom]' => 'Doe',
            'registration_form[prenom]' => 'John',
            'registration_form[tel_gsm]' => '0612345678',
            'registration_form[email]' => 'john.doe@example.com',
            'registration_form[role]' => 'ROLE_USER',
            'registration_form[plainPassword]' => 'Password123!',
            'registration_form[agreeTerms]' => '1',
        ]);

        $this->client->submit($form);
        
        // Vérifier la redirection après inscription réussie
        $this->assertResponseRedirects('/dashboard');
        
        // Vérifier que l'utilisateur a bien été créé en base de données
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => 'john.doe@example.com']);
        $this->assertNotNull($user);
        $this->assertEquals('Doe', $user->getNom());
        $this->assertEquals('John', $user->getPrenom());
        $this->assertEquals('0612345678', $user->getTelGsm());
        $this->assertEquals(['ROLE_USER'], $user->getRoles());
    }

    public function testRegisterWithInvalidEmail(): void
    {
        $crawler = $this->client->request('GET', $this->path);
        
        $form = $crawler->selectButton('S\'inscrire')->form([
            'registration_form[nom]' => 'Doe',
            'registration_form[prenom]' => 'John',
            'registration_form[tel_gsm]' => '0612345678',
            'registration_form[email]' => 'invalid-email',
            'registration_form[role]' => 'ROLE_USER',
            'registration_form[plainPassword]' => 'Password123!',
            'registration_form[agreeTerms]' => '1',
        ]);

        $this->client->submit($form);
        
        $this->assertResponseIsUnprocessable();
        $this->assertStringContainsString('Cette valeur n\'est pas une adresse email valide.', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithWeakPassword(): void
    {
        $crawler = $this->client->request('GET', $this->path);
        
        $form = $crawler->selectButton('S\'inscrire')->form([
            'registration_form[nom]' => 'Doe',
            'registration_form[prenom]' => 'John',
            'registration_form[tel_gsm]' => '0612345678',
            'registration_form[email]' => 'john.doe@example.com',
            'registration_form[role]' => 'ROLE_USER',
            'registration_form[plainPassword]' => '123',
            'registration_form[agreeTerms]' => '1',
        ]);

        $this->client->submit($form);
        
        $this->assertResponseIsUnprocessable();
        $this->assertStringContainsString('Votre mot de passe doit contenir au moins 12 caractères', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithoutAgreeingToTerms(): void
    {
        $crawler = $this->client->request('GET', $this->path);
        
        $form = $crawler->selectButton('S\'inscrire')->form([
            'registration_form[nom]' => 'Doe',
            'registration_form[prenom]' => 'John',
            'registration_form[tel_gsm]' => '0612345678',
            'registration_form[email]' => 'john.doe@example.com',
            'registration_form[role]' => 'ROLE_USER',
            'registration_form[plainPassword]' => 'Password123!',
            // Ne pas cocher la case des conditions d'utilisation
        ]);

        $this->client->submit($form);
        
        $this->assertResponseIsUnprocessable();
        $this->assertStringContainsString('Vous devez accepter nos conditions', $this->client->getResponse()->getContent());
    }

    public function testRegisterWithExistingEmail(): void
    {
        // Créer un utilisateur existant
        $existingUser = new User();
        $existingUser->setEmail('existing@example.com');
        $existingUser->setNom('Doe');
        $existingUser->setPrenom('John');
        $existingUser->setTelGsm('0612345678');
        $existingUser->setPassword($this->passwordHasher->hashPassword($existingUser, 'Password123!'));
        $existingUser->setRoles(['ROLE_USER']);
        
        $this->entityManager->persist($existingUser);
        $this->entityManager->flush();

        // Essayer de créer un nouvel utilisateur avec le même email
        $crawler = $this->client->request('GET', $this->path);
        
        $form = $crawler->selectButton('S\'inscrire')->form([
            'registration_form[nom]' => 'Doe',
            'registration_form[prenom]' => 'John',
            'registration_form[tel_gsm]' => '0612345678',
            'registration_form[email]' => 'existing@example.com',
            'registration_form[role]' => 'ROLE_USER',
            'registration_form[plainPassword]' => 'Password123!',
            'registration_form[agreeTerms]' => '1',
        ]);

        $this->client->submit($form);
        
        $this->assertResponseIsUnprocessable();
        $this->assertStringContainsString('Il existe déjà un compte avec cet email', $this->client->getResponse()->getContent());
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        
        // Fermer la connexion à la base de données
        $this->entityManager->close();
    }
}
