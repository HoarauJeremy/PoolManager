<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Enum\Status;
use App\Form\InterventionType;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur gérant les opérations CRUD et les actions spécifiques sur les interventions.
 * 
 * Chaque méthode correspond à une route et une action (liste, création, édition, suppression, etc.)
 */
#[Route('/intervention')]
final class InterventionController extends AbstractController
{
    /**
     * Affiche la liste de toutes les interventions enregistrées.
     *
     * Méthode GET : accessible via l’URL /intervention
     */
    #[Route(name: 'app_intervention_index', methods: ['GET'])]
    public function index(InterventionRepository $interventionRepository): Response
    {
        // Récupère toutes les interventions en base de données via le repository
        $interventions = $interventionRepository->findAll();

        // Envoie les données à la vue Twig pour affichage
        return $this->render('intervention/index.html.twig', [
            'interventions' => $interventions,
        ]);
    }

    /**
     * Crée une nouvelle intervention.
     *
     * Méthodes GET/POST : /intervention/new
     * - GET : affiche le formulaire
     * - POST : traite la soumission du formulaire
     */
    #[Route('/new', name: 'app_intervention_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée une nouvelle instance d'Intervention vide
        $intervention = new Intervention();

        // Génère le formulaire à partir du type InterventionType
        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request); // Lie la requête HTTP au formulaire

        // Si le formulaire est soumis et valide, on enregistre en base
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($intervention);
            $entityManager->flush();

            // Redirection vers la liste après création
            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche la vue du formulaire
        return $this->render('intervention/new.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

    /**
     * Affiche les détails d’une intervention donnée.
     *
     * Méthode GET : /intervention/{id}
     */
    #[Route('/{id}', name: 'app_intervention_show', methods: ['GET'])]
    public function show(Intervention $intervention): Response
    {
        // La variable $intervention est automatiquement injectée grâce au ParamConverter
        return $this->render('intervention/show.html.twig', [
            'intervention' => $intervention,
        ]);
    }

    /**
     * Édite une intervention existante.
     *
     * Méthodes GET/POST : /intervention/{id}/edit
     */
    #[Route('/{id}/edit', name: 'app_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        // Crée un formulaire pré-rempli avec les données existantes
        $form = $this->createForm(InterventionType::class, $intervention);
        $form->handleRequest($request);

        // Sauvegarde les modifications si le formulaire est valide
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            // Redirection vers la liste après modification
            return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche la vue du formulaire d’édition
        return $this->render('intervention/edit.html.twig', [
            'intervention' => $intervention,
            'form' => $form,
        ]);
    }

    /**
     * Passe une intervention au statut "En cours".
     *
     * Méthode POST : /intervention/{id}/start
     */
    #[Route('/{id}/start', name: 'app_intervention_start', methods: ['POST'])]
    public function start(Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        // Vérifie que l’intervention est actuellement planifiée avant de la démarrer
        if ($intervention->getStatus() === Status::PLANIFIER) {
            $intervention->setStatus(Status::ENCOURS);
            $entityManager->flush();

            // Message flash de succès affiché sur la page suivante
            $this->addFlash('success', 'L\'intervention a été passée en cours.');
        } else {
            // Message d’erreur si le statut n’est pas cohérent
            $this->addFlash('error', 'Cette intervention ne peut pas être passée en cours.');
        }

        // Redirige vers le tableau de bord (page d’accueil ou gestion)
        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * Supprime une intervention.
     *
     * Méthode POST : /intervention/{id}
     */
    #[Route('/{id}', name: 'app_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, Intervention $intervention, EntityManagerInterface $entityManager): Response
    {
        // Vérifie que seul un administrateur peut supprimer une intervention
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Vérifie la validité du token CSRF pour sécuriser la suppression
        if ($this->isCsrfTokenValid('delete' . $intervention->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($intervention);
            $entityManager->flush();
        }

        // Redirection vers la liste après suppression
        return $this->redirectToRoute('app_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
}
