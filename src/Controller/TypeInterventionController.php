<?php

namespace App\Controller;

use App\Entity\TypeIntervention; // L'entité représentant un type d’intervention (table en base de données)
use App\Form\TypeInterventionType; // Le formulaire associé à l'entité
use App\Repository\TypeInterventionRepository; // Le repository pour effectuer des requêtes sur TypeIntervention
use Doctrine\ORM\EntityManagerInterface; // Interface pour gérer les entités (insert, update, delete)
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController; // Classe de base des contrôleurs Symfony
use Symfony\Component\HttpFoundation\Request; // Représente la requête HTTP
use Symfony\Component\HttpFoundation\Response; // Représente la réponse HTTP
use Symfony\Component\Routing\Attribute\Route; // Permet de définir les routes directement via des attributs PHP 8+

// Déclare une route "globale" pour toutes les actions de ce contrôleur
#[Route('/type/intervention')]
final class TypeInterventionController extends AbstractController
{
    /**
     * Liste tous les types d’interventions.
     * Route : /type/intervention
     * Méthode HTTP : GET
     */
    #[Route(name: 'app_type_intervention_index', methods: ['GET'])]
    public function index(TypeInterventionRepository $typeInterventionRepository): Response
    {
        // Récupère tous les types d’interventions via le repository
        $types = $typeInterventionRepository->findAll();

        // Rend la vue Twig en passant la liste récupérée
        return $this->render('type_intervention/index.html.twig', [
            'type_interventions' => $types,
        ]);
    }

    /**
     * Crée un nouveau type d’intervention.
     * Route : /type/intervention/new
     * Méthodes HTTP : GET (affiche le formulaire) / POST (traite la soumission)
     */
    #[Route('/new', name: 'app_type_intervention_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        // Crée une nouvelle instance vide de TypeIntervention
        $typeIntervention = new TypeIntervention();

        // Crée le formulaire lié à cette entité
        $form = $this->createForm(TypeInterventionType::class, $typeIntervention);
        $form->handleRequest($request); // Lie le formulaire à la requête HTTP

        // Si le formulaire est soumis et valide, on enregistre l’entité
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($typeIntervention); // Prépare l’insertion
            $entityManager->flush(); // Exécute la requête SQL

            // Redirige vers la page de liste après création
            return $this->redirectToRoute('app_type_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        // Si le formulaire n’est pas encore soumis, on affiche la page
        return $this->render('type_intervention/new.html.twig', [
            'type_intervention' => $typeIntervention,
            'form' => $form,
        ]);
    }

    /**
     * Affiche un type d’intervention spécifique.
     * Route : /type/intervention/{id}
     * Méthode HTTP : GET
     */
    #[Route('/{id}', name: 'app_type_intervention_show', methods: ['GET'])]
    public function show(TypeIntervention $typeIntervention): Response
    {
        // Symfony injecte automatiquement l’entité correspondant à l’ID passé dans l’URL
        return $this->render('type_intervention/show.html.twig', [
            'type_intervention' => $typeIntervention,
        ]);
    }

    /**
     * Édite un type d’intervention existant.
     * Route : /type/intervention/{id}/edit
     * Méthodes HTTP : GET (affiche le formulaire) / POST (soumet les modifications)
     */
    #[Route('/{id}/edit', name: 'app_type_intervention_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TypeIntervention $typeIntervention, EntityManagerInterface $entityManager): Response
    {
        // Crée le formulaire pré-rempli avec les données existantes
        $form = $this->createForm(TypeInterventionType::class, $typeIntervention);
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide, on sauvegarde les changements
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); // Pas besoin de persist() car l’objet existe déjà

            return $this->redirectToRoute('app_type_intervention_index', [], Response::HTTP_SEE_OTHER);
        }

        // Affiche la vue d’édition avec le formulaire
        return $this->render('type_intervention/edit.html.twig', [
            'type_intervention' => $typeIntervention,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un type d’intervention.
     * Route : /type/intervention/{id}
     * Méthode HTTP : POST
     * ⚠️ Même chemin que "show", mais différencié par la méthode HTTP.
     */
    #[Route('/{id}', name: 'app_type_intervention_delete', methods: ['POST'])]
    public function delete(Request $request, TypeIntervention $typeIntervention, EntityManagerInterface $entityManager): Response
    {
        // Sécurité : seules les personnes avec le rôle ADMIN peuvent supprimer
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        
        // Vérifie le token CSRF pour éviter les suppressions non autorisées
        if ($this->isCsrfTokenValid('delete' . $typeIntervention->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($typeIntervention); // Prépare la suppression
            $entityManager->flush(); // Exécute en base de données
        }

        // Redirige vers la page de liste après suppression
        return $this->redirectToRoute('app_type_intervention_index', [], Response::HTTP_SEE_OTHER);
    }
}
