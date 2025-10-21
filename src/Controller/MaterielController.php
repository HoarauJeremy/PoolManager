<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Form\MaterielType;
use App\Repository\MaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Contrôleur pour la gestion des matériels
 * 
 * Ce contrôleur gère toutes les opérations CRUD (Create, Read, Update, Delete)
 * pour l'entité Materiel. Il permet de lister, créer, afficher, modifier et
 * supprimer des matériels dans le système de gestion de piscine.
 */
#[Route('/materiel')]
final class MaterielController extends AbstractController
{
    /**
     * Affiche la liste de tous les matériels
     * 
     * Cette méthode récupère tous les matériels de la base de données
     * et les affiche dans une vue de liste.
     * 
     * @param MaterielRepository $materielRepository Repository pour accéder aux données des matériels
     * @return Response Vue contenant la liste des matériels
     */
    #[Route(name: 'app_materiel_index', methods: ['GET'])]
    public function index(MaterielRepository $materielRepository): Response
    {
        return $this->render('materiel/index.html.twig', [
            'materiels' => $materielRepository->findAll(),
        ]);
    }

    /**
     * Crée un nouveau matériel
     * 
     * Cette méthode gère l'affichage du formulaire de création et
     * la soumission des données pour créer un nouveau matériel.
     * 
     * @param Request $request Requête HTTP contenant les données du formulaire
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour la persistance
     * @return Response Vue du formulaire de création ou redirection vers la liste
     */
    #[Route('/new', name: 'app_materiel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $materiel = new Materiel();
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($materiel);
            $entityManager->flush();

            return $this->redirectToRoute('app_materiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('materiel/new.html.twig', [
            'materiel' => $materiel,
            'form' => $form,
        ]);
    }

    /**
     * Affiche les détails d'un matériel spécifique
     * 
     * Cette méthode affiche les informations détaillées d'un matériel
     * identifié par son ID.
     * 
     * @param Materiel $materiel Le matériel à afficher (résolu automatiquement par Symfony)
     * @return Response Vue contenant les détails du matériel
     */
    #[Route('/{id}', name: 'app_materiel_show', methods: ['GET'])]
    public function show(Materiel $materiel): Response
    {
        return $this->render('materiel/show.html.twig', [
            'materiel' => $materiel,
        ]);
    }

    /**
     * Modifie un matériel existant
     * 
     * Cette méthode gère l'affichage du formulaire de modification et
     * la soumission des données pour mettre à jour un matériel existant.
     * 
     * @param Request $request Requête HTTP contenant les données du formulaire
     * @param Materiel $materiel Le matériel à modifier (résolu automatiquement par Symfony)
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour la persistance
     * @return Response Vue du formulaire de modification ou redirection vers la liste
     */
    #[Route('/{id}/edit', name: 'app_materiel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Materiel $materiel, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_materiel_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('materiel/edit.html.twig', [
            'materiel' => $materiel,
            'form' => $form,
        ]);
    }

    /**
     * Supprime un matériel
     * 
     * Cette méthode supprime un matériel de la base de données après
     * vérification du token CSRF pour la sécurité.
     * 
     * @param Request $request Requête HTTP contenant le token CSRF
     * @param Materiel $materiel Le matériel à supprimer (résolu automatiquement par Symfony)
     * @param EntityManagerInterface $entityManager Gestionnaire d'entités pour la persistance
     * @return Response Redirection vers la liste des matériels
     */
    #[Route('/{id}', name: 'app_materiel_delete', methods: ['POST'])]
    public function delete(Request $request, Materiel $materiel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$materiel->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($materiel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_materiel_index', [], Response::HTTP_SEE_OTHER);
    }
}
