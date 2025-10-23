<?php

namespace App\Controller;

use App\Enum\Status;
use App\Repository\InterventionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        return new Response('OK');
    }

    private function renderAdminDashboard(InterventionRepository $repository, int $page, int $limit, string $statusFilter = 'all'): Response
    {
        // Convertir le filtre en Status enum
        $status = null;
        if ($statusFilter !== 'all') {
            $status = match($statusFilter) {
                'planifiees' => Status::PLANIFIER,
                'en_cours' => Status::ENCOURS,
                'terminees' => Status::TERMINER,
                'annulees' => Status::ANNULER,
                default => null
            };
        }

        // Récupération des interventions paginées avec filtre
        $query = $repository->findPaginatedWithFilter($page, $limit, $status);
        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        // Statistiques par statut
        $statsParStatut = [
            'planifiees' => $repository->count(['status' => Status::PLANIFIER]),
            'en_cours' => $repository->count(['status' => Status::ENCOURS]),
            'terminees' => $repository->count(['status' => Status::TERMINER]),
            'annulees' => $repository->count(['status' => Status::ANNULER])
        ];

        // Statistiques pour les graphiques
        $stats = [
            'interventions_par_mois' => $repository->getInterventionsPerMonth(),
            'interventions_par_type' => $repository->getInterventionsPerType(),
            'interventions_par_statut' => $statsParStatut
        ];

        // Récupérer les interventions du mois actuel pour le mini calendrier
        $interventionsMoisActuel = $repository->findInterventionsByMonth((int)date('Y'), (int)date('m'));

        return $this->render('dashboard/admin.html.twig', [
            'interventions' => $paginator,
            'stats' => $stats,
            'interventions_mois' => $interventionsMoisActuel,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'statusFilter' => $statusFilter,
        ]);
    }

    private function renderUserDashboard(InterventionRepository $interventionRepository, UserRepository $userRepository, $user, int $page, int $limit): Response
    {
        $userId = $user->getId();

        // Récupération des interventions via le repository (limité à 6 max)
        $interventionsAVenir = $interventionRepository->findUpcomingByUser($userId, 2);
        $interventionsEnCours = array_slice($interventionRepository->findInProgressByUser($userId), 0, 1);

        // Historique avec pagination
        $query = $interventionRepository->findHistoryByUser($userId, $page, $limit);
        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        // Statistiques par statut
        $statsParStatut = [
            'planifiees' => $interventionRepository->countByUserAndStatus($userId, Status::PLANIFIER),
            'en_cours' => $interventionRepository->countByUserAndStatus($userId, Status::ENCOURS),
            'terminees' => $interventionRepository->countByUserAndStatus($userId, Status::TERMINER),
        ];

        // Statistiques pour les graphiques
        $stats = [
            'interventions_par_mois' => $interventionRepository->getInterventionsPerMonthByUser($userId),
            'interventions_par_statut' => $statsParStatut
        ];

        // Récupérer les interventions du mois actuel pour le mini calendrier
        $interventionsMoisActuel = $interventionRepository->findInterventionsByMonthAndUser((int)date('Y'), (int)date('m'), $userId);

        return $this->render('dashboard/user.html.twig', [
            'interventions_a_venir' => $interventionsAVenir,
            'interventions_en_cours' => $interventionsEnCours,
            'historique' => $paginator,
            'stats' => $stats,
            'activites' => $interventionRepository->getRecentActivities(9),
            'interventions_mois' => $interventionsMoisActuel,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

}
