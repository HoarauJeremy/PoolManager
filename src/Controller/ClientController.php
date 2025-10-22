<?php

namespace App\Controller;

// Import des classes nécessaires
use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

// Définition du préfixe de route pour toutes les actions du contrôleur
#[Route('/client')]
final class ClientController extends AbstractController
{
    // Liste tous les clients (page d'accueil)
    #[Route(name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        // Rendu de la vue index avec la liste de tous les clients
        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    // Crée un nouveau client
    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Création d'une nouvelle instance de Client
        $client = new Client();
        
        // Création du formulaire basé sur ClientType
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrement du nouveau client en base de données
            $entityManager->persist($client);
            $entityManager->flush();

            // Redirection vers la liste des clients
            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affichage du formulaire de création
        return $this->render('client/new.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    // Affiche les détails d'un client spécifique
    #[Route('/{id}', name: 'app_client_show', methods: ['GET'])]
    public function show(Client $client): Response
    {
        // Rendu de la vue de détail avec le client demandé
        return $this->render('client/show.html.twig', [
            'client' => $client,
        ]);
    }

    // Modifie un client existant
    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        // Création du formulaire pré-rempli avec les données du client existant
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        // Vérification de la soumission et de la validité du formulaire
        if ($form->isSubmitted() && $form->isValid()) {
            // Mise à jour du client en base de données
            $entityManager->flush();

            // Redirection vers la liste des clients
            return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affichage du formulaire d'édition
        return $this->render('client/edit.html.twig', [
            'client' => $client,
            'form' => $form,
        ]);
    }

    // Supprime un client
    #[Route('/{id}', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client, EntityManagerInterface $entityManager): Response
    {
        // Vérification des droits d'accès (nécessite le rôle ADMIN)
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérification du token CSRF pour prévenir les attaques
        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->getPayload()->getString('_token'))) {
            // Suppression du client de la base de données
            $entityManager->remove($client);
            $entityManager->flush();
        }

        // Redirection vers la liste des clients
        return $this->redirectToRoute('app_client_index', [], Response::HTTP_SEE_OTHER);
    }
}