<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Intervention;
use App\Entity\TypeIntervention;
use App\Entity\User;
use App\Enum\Status;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        // Création des types d'intervention
        $types = [];
        $typesData = [
            'Entretien piscine',
            'Réparation pompe',
            'Nettoyage filtre',
            'Traitement eau',
            'Installation équipement',
            'Diagnostic technique',
        ];

        foreach ($typesData as $nomType) {
            $type = new TypeIntervention();
            $type->setNom($nomType);
            $manager->persist($type);
            $types[] = $type;
        }

        // Création de l'administrateur
        $admin = new User();
        $admin->setEmail('admin@poolmanager.com');
        $admin->setNom('Admin');
        $admin->setPrenom('Administrateur');
        $admin->setPassword(password_hash('admin123', PASSWORD_DEFAULT));
        $admin->setRoles(['ROLE_ADMIN', 'ROLE_USER']);
        $manager->persist($admin);

        // Création des utilisateurs (techniciens)
        $users = [];
        $usersData = [
            ['email' => 'jean.dupont@poolmanager.com', 'nom' => 'Dupont', 'prenom' => 'Jean'],
            ['email' => 'marie.martin@poolmanager.com', 'nom' => 'Martin', 'prenom' => 'Marie'],
            ['email' => 'pierre.bernard@poolmanager.com', 'nom' => 'Bernard', 'prenom' => 'Pierre'],
            ['email' => 'sophie.leroy@poolmanager.com', 'nom' => 'Leroy', 'prenom' => 'Sophie'],
        ];

        foreach ($usersData as $userData) {
            $user = new User();
            $user->setEmail($userData['email']);
            $user->setNom($userData['nom']);
            $user->setPrenom($userData['prenom']);
            $user->setPassword(password_hash('password123', PASSWORD_DEFAULT));
            $user->setRoles(['ROLE_USER']);
            $manager->persist($user);
            $users[] = $user;
        }

        // Création des clients
        $clients = [];
        $clientsData = [
            ['nom' => 'Paradise', 'prenom' => 'Hôtel', 'email' => 'contact@hotelparadise.fr', 'adresse' => '15 Avenue des Palmiers', 'ville' => 'Nice', 'code_postal' => '06000', 'tel_gsm' => 493123456],
            ['nom' => 'Terrasses', 'prenom' => 'Résidence Les', 'email' => 'contact@lesterrasses.fr', 'adresse' => '42 Rue de la République', 'ville' => 'Cannes', 'code_postal' => '06400', 'tel_gsm' => 493234567],
            ['nom' => 'Bellevue', 'prenom' => 'Villa', 'email' => 'contact@villabellevue.fr', 'adresse' => '8 Chemin des Collines', 'ville' => 'Antibes', 'code_postal' => '06600', 'tel_gsm' => 493345678],
            ['nom' => 'Les Pins', 'prenom' => 'Camping', 'email' => 'contact@campinglespins.fr', 'adresse' => '123 Route de la Mer', 'ville' => 'Fréjus', 'code_postal' => '83600', 'tel_gsm' => 494456789],
            ['nom' => 'Municipal', 'prenom' => 'Centre Aquatique', 'email' => 'contact@centreaquatique.fr', 'adresse' => '5 Place du Sport', 'ville' => 'Toulon', 'code_postal' => '83000', 'tel_gsm' => 494567890],
            ['nom' => 'Cap d\'Azur', 'prenom' => 'Résidence', 'email' => 'contact@capdazur.fr', 'adresse' => '28 Boulevard Maritime', 'ville' => 'Saint-Tropez', 'code_postal' => '83990', 'tel_gsm' => 494678901],
        ];

        foreach ($clientsData as $clientData) {
            $client = new Client();
            $client->setNom($clientData['nom']);
            $client->setPrenom($clientData['prenom']);
            $client->setEmail($clientData['email']);
            $client->setAdresse($clientData['adresse']);
            $client->setVille($clientData['ville']);
            $client->setCodePostal((int)$clientData['code_postal']);
            $client->setTelGsm($clientData['tel_gsm']);
            $manager->persist($client);
            $clients[] = $client;
        }

        // Création des interventions sur les 10 derniers mois
        $statuses = [Status::PLANIFIER, Status::ENCOURS, Status::TERMINER, Status::ANNULER];
        $interventionsCount = 0;

        for ($monthOffset = 9; $monthOffset >= 0; $monthOffset--) {
            // Nombre d'interventions par mois (varie entre 3 et 8)
            $nbInterventions = rand(3, 8);

            for ($i = 0; $i < $nbInterventions; $i++) {
                $intervention = new Intervention();

                // Libellé
                $randomType = $types[array_rand($types)];
                $randomClient = $clients[array_rand($clients)];
                $intervention->setLibelle($randomType->getNom() . ' - ' . $randomClient->getNom());

                // Type
                $intervention->setType($randomType);

                // Client
                $intervention->setClient($randomClient);

                // Dates (dans le mois en cours - $monthOffset)
                $dateDebut = new \DateTimeImmutable();
                $dateDebut = $dateDebut->modify("-$monthOffset months");
                $day = rand(1, 28);
                $hour = rand(8, 17);
                $dateDebut = $dateDebut->setDate(
                    (int)$dateDebut->format('Y'),
                    (int)$dateDebut->format('m'),
                    $day
                )->setTime($hour, 0);

                $dateFin = $dateDebut->modify('+' . rand(1, 4) . ' hours');

                $intervention->setDateDebut($dateDebut);
                $intervention->setDateFin($dateFin);

                // Adresse (même que le client)
                $intervention->setAdresse($randomClient->getAdresse());
                $intervention->setVille($randomClient->getVille());
                $intervention->setCodePostal($randomClient->getCodePostal());

                // Informations complémentaires
                $infos = [
                    'Intervention standard',
                    'Vérification complète du système',
                    'Maintenance préventive',
                    'Intervention d\'urgence',
                    'Contrôle qualité de l\'eau',
                    'Remplacement de pièces',
                ];
                $intervention->setInfos($infos[array_rand($infos)]);

                // Statut (les interventions passées sont plus souvent terminées)
                if ($monthOffset > 2) {
                    // Interventions anciennes : principalement terminées ou annulées
                    $statusChoice = rand(0, 100);
                    if ($statusChoice < 70) {
                        $intervention->setStatus(Status::TERMINER);
                    } elseif ($statusChoice < 85) {
                        $intervention->setStatus(Status::ANNULER);
                    } elseif ($statusChoice < 95) {
                        $intervention->setStatus(Status::ENCOURS);
                    } else {
                        $intervention->setStatus(Status::PLANIFIER);
                    }
                } elseif ($monthOffset > 0) {
                    // Interventions récentes : mix de statuts
                    $intervention->setStatus($statuses[array_rand($statuses)]);
                } else {
                    // Interventions du mois en cours : principalement planifiées ou en cours
                    $statusChoice = rand(0, 100);
                    if ($statusChoice < 50) {
                        $intervention->setStatus(Status::PLANIFIER);
                    } elseif ($statusChoice < 85) {
                        $intervention->setStatus(Status::ENCOURS);
                    } elseif ($statusChoice < 95) {
                        $intervention->setStatus(Status::TERMINER);
                    } else {
                        $intervention->setStatus(Status::ANNULER);
                    }
                }

                // Techniciens (1 à 3 techniciens par intervention)
                $nbTechniciens = rand(1, 3);
                $selectedUsers = array_rand($users, min($nbTechniciens, count($users)));
                if (!is_array($selectedUsers)) {
                    $selectedUsers = [$selectedUsers];
                }
                foreach ($selectedUsers as $userIndex) {
                    $intervention->addTechnicien($users[$userIndex]);
                }

                $manager->persist($intervention);
                $interventionsCount++;
            }
        }

        $manager->flush();

        echo "Fixtures chargées avec succès !\n";
        echo "- " . count($types) . " types d'intervention\n";
        echo "- " . count($users) . " utilisateurs\n";
        echo "- " . count($clients) . " clients\n";
        echo "- " . $interventionsCount . " interventions\n";
    }
}
