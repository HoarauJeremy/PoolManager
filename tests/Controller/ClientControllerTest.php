<?php

namespace App\Tests\Controller;

use App\Entity\Client;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ClientControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $clientRepository;
    private string $path = '/client/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->clientRepository = $this->manager->getRepository(Client::class);

        foreach ($this->clientRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'client[nom]' => 'Testing',
            'client[prenom]' => 'Testing',
            'client[email]' => 'Testing',
            'client[adresse]' => 'Testing',
            'client[ville]' => 'Testing',
            'client[code_postal]' => 'Testing',
            'client[tel_fixe]' => 'Testing',
            'client[tel_gsm]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->clientRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setNom('My Title');
        $fixture->setPrenom('My Title');
        $fixture->setEmail('My Title');
        $fixture->setAdresse('My Title');
        $fixture->setVille('My Title');
        $fixture->setCode_postal('My Title');
        $fixture->setTel_fixe('My Title');
        $fixture->setTel_gsm('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Client');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setNom('Value');
        $fixture->setPrenom('Value');
        $fixture->setEmail('Value');
        $fixture->setAdresse('Value');
        $fixture->setVille('Value');
        $fixture->setCode_postal('Value');
        $fixture->setTel_fixe('Value');
        $fixture->setTel_gsm('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'client[nom]' => 'Something New',
            'client[prenom]' => 'Something New',
            'client[email]' => 'Something New',
            'client[adresse]' => 'Something New',
            'client[ville]' => 'Something New',
            'client[code_postal]' => 'Something New',
            'client[tel_fixe]' => 'Something New',
            'client[tel_gsm]' => 'Something New',
        ]);

        self::assertResponseRedirects('/client/');

        $fixture = $this->clientRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
        self::assertSame('Something New', $fixture[0]->getPrenom());
        self::assertSame('Something New', $fixture[0]->getEmail());
        self::assertSame('Something New', $fixture[0]->getAdresse());
        self::assertSame('Something New', $fixture[0]->getVille());
        self::assertSame('Something New', $fixture[0]->getCode_postal());
        self::assertSame('Something New', $fixture[0]->getTel_fixe());
        self::assertSame('Something New', $fixture[0]->getTel_gsm());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Client();
        $fixture->setNom('Value');
        $fixture->setPrenom('Value');
        $fixture->setEmail('Value');
        $fixture->setAdresse('Value');
        $fixture->setVille('Value');
        $fixture->setCode_postal('Value');
        $fixture->setTel_fixe('Value');
        $fixture->setTel_gsm('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/client/');
        self::assertSame(0, $this->clientRepository->count([]));
    }
}
