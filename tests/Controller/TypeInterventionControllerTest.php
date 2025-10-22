<?php

namespace App\Tests\Controller;

use App\Entity\TypeIntervention;
use App\Repository\TypeInterventionRepository;
use App\Tests\AuthenticatedWebTestCase;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

final class TypeInterventionControllerTest extends AuthenticatedWebTestCase
{
    private EntityManagerInterface $manager;
    private EntityRepository $typeInterventionRepository;
    private string $path = '/type/intervention/';

    protected function setUp(): void
    {
        parent::setUp(); // Authentifie automatiquement l'utilisateur
        
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->typeInterventionRepository = $this->manager->getRepository(TypeIntervention::class);

        foreach ($this->typeInterventionRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Liste des Types d\'interventions');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'type_intervention[nom]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->typeInterventionRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new TypeIntervention();
        $fixture->setNom('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('TypeIntervention');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new TypeIntervention();
        $fixture->setNom('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'type_intervention[nom]' => 'Something New',
        ]);

        self::assertResponseRedirects('/type/intervention/');

        $fixture = $this->typeInterventionRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getNom());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new TypeIntervention();
        $fixture->setNom('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/type/intervention/');
        self::assertSame(0, $this->typeInterventionRepository->count([]));
    }
}
