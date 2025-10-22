<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Enum\Status;
use App\Repository\InterventionRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(Request $request, InterventionRepository $interventionRepository, UserRepository $userRepository): Response
    {
        $user = $this->getUser();
        $isAdmin = in_array('ROLE_ADMIN', $user->getRoles());

        // Pagination
        $page = max(1, $request->query->getInt('page', 1));
        $limit = 10;

        // Filtre de statut
        $statusFilter = $request->query->get('status', 'all');

        if ($isAdmin) {
            // Dashboard Admin
            return $this->renderAdminDashboard($interventionRepository, $page, $limit, $statusFilter);
        } else {
            // Dashboard Technicien
            return $this->renderUserDashboard($interventionRepository, $userRepository, $user, $page, $limit);
        }
    }

    private function renderAdminDashboard(InterventionRepository $repository, int $page, int $limit, string $statusFilter = 'all'): Response
    {
        // Récupération des interventions avec pagination et filtre
        $queryBuilder = $repository->createQueryBuilder('i');

        // Appliquer le filtre de statut si nécessaire
        if ($statusFilter !== 'all') {
            $status = match($statusFilter) {
                'planifiees' => Status::PLANIFIER,
                'en_cours' => Status::ENCOURS,
                'terminees' => Status::TERMINER,
                'annulees' => Status::ANNULER,
                default => null
            };

            if ($status !== null) {
                $queryBuilder->where('i.status = :status')
                    ->setParameter('status', $status);
            }
        }

        $query = $queryBuilder
            ->orderBy('i.date_debut', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        // Calcul des statistiques par statut
        $statsParStatut = [
            'planifiees' => $repository->count(['status' => Status::PLANIFIER]),
            'en_cours' => $repository->count(['status' => Status::ENCOURS]),
            'terminees' => $repository->count(['status' => Status::TERMINER]),
            'annulees' => $repository->count(['status' => Status::ANNULER])
        ];

        // Calcul des interventions par mois
        $interventionsParMois = $this->getInterventionsParMois($repository);

        // Calcul des interventions par type
        $interventionsParType = $this->getInterventionsParType($repository);

        // Statistiques pour les graphiques
        $stats = [
            'interventions_par_mois' => $interventionsParMois,
            'interventions_par_type' => $interventionsParType,
            'interventions_par_statut' => $statsParStatut
        ];

        return $this->render('dashboard/admin.html.twig', [
            'interventions' => $paginator,
            'stats' => $stats,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
            'statusFilter' => $statusFilter,
        ]);
    }

    private function renderUserDashboard(InterventionRepository $interventionRepository, UserRepository $userRepository, $user, int $page, int $limit): Response
    {
        // Récupération des interventions à venir (planifiées)
        $interventionsAVenir = $interventionRepository->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->where('t.id = :userId')
            ->andWhere('i.status = :status')
            ->setParameter('userId', $user->getId())
            ->setParameter('status', Status::PLANIFIER)
            ->orderBy('i.date_debut', 'ASC')
            ->setMaxResults(5)
            ->getQuery()
            ->getResult();

        // Récupération des interventions en cours
        $interventionsEnCours = $interventionRepository->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->where('t.id = :userId')
            ->andWhere('i.status = :status')
            ->setParameter('userId', $user->getId())
            ->setParameter('status', Status::ENCOURS)
            ->orderBy('i.date_debut', 'DESC')
            ->getQuery()
            ->getResult();

        // Récupération de l'historique (terminées et annulées) avec pagination
        $query = $interventionRepository->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->where('t.id = :userId')
            ->andWhere('i.status IN (:statuses)')
            ->setParameter('userId', $user->getId())
            ->setParameter('statuses', [Status::TERMINER, Status::ANNULER])
            ->orderBy('i.date_debut', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();

        $paginator = new Paginator($query);
        $totalItems = count($paginator);
        $totalPages = ceil($totalItems / $limit);

        // Statistiques des interventions du technicien
        $statsParStatut = [
            'planifiees' => $interventionRepository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->leftJoin('i.technicens', 't')
                ->where('t.id = :userId')
                ->andWhere('i.status = :status')
                ->setParameter('userId', $user->getId())
                ->setParameter('status', Status::PLANIFIER)
                ->getQuery()
                ->getSingleScalarResult(),
            'en_cours' => $interventionRepository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->leftJoin('i.technicens', 't')
                ->where('t.id = :userId')
                ->andWhere('i.status = :status')
                ->setParameter('userId', $user->getId())
                ->setParameter('status', Status::ENCOURS)
                ->getQuery()
                ->getSingleScalarResult(),
            'terminees' => $interventionRepository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->leftJoin('i.technicens', 't')
                ->where('t.id = :userId')
                ->andWhere('i.status = :status')
                ->setParameter('userId', $user->getId())
                ->setParameter('status', Status::TERMINER)
                ->getQuery()
                ->getSingleScalarResult(),
        ];

        // Interventions par mois pour le technicien
        $interventionsParMois = $this->getUserInterventionsParMois($interventionRepository, $user->getId());

        // Activités récentes de tous les techniciens
        $activitesRecentes = $this->getActivitesRecentes($interventionRepository);

        $stats = [
            'interventions_par_mois' => $interventionsParMois,
            'interventions_par_statut' => $statsParStatut
        ];

        return $this->render('dashboard/user.html.twig', [
            'interventions_a_venir' => $interventionsAVenir,
            'interventions_en_cours' => $interventionsEnCours,
            'historique' => $paginator,
            'stats' => $stats,
            'activites' => $activitesRecentes,
            'currentPage' => $page,
            'totalPages' => $totalPages,
            'totalItems' => $totalItems,
        ]);
    }

    private function getInterventionsParMois(InterventionRepository $repository): array
    {
        $labels = [];
        $data = [];

        // Générer les 10 derniers mois
        for ($i = 9; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i months");
            $labels[] = $date->format('M');

            // Compter les interventions pour ce mois
            $startDate = new \DateTime($date->format('Y-m-01'));
            $endDate = clone $startDate;
            $endDate->modify('last day of this month')->setTime(23, 59, 59);

            $count = $repository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->where('i.date_debut >= :start')
                ->andWhere('i.date_debut <= :end')
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->getQuery()
                ->getSingleScalarResult();

            $data[] = (int) $count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getInterventionsParType(InterventionRepository $repository): array
    {
        $results = $repository->createQueryBuilder('i')
            ->select('t.nom as label, COUNT(i.id) as count')
            ->leftJoin('i.type', 't')
            ->groupBy('t.id')
            ->getQuery()
            ->getResult();

        $labels = [];
        $data = [];

        foreach ($results as $result) {
            $labels[] = $result['label'] ?? 'Non défini';
            $data[] = (int) $result['count'];
        }

        // Si aucune donnée, retourner des tableaux vides
        if (empty($labels)) {
            return [
                'labels' => [],
                'data' => []
            ];
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getUserInterventionsParMois(InterventionRepository $repository, int $userId): array
    {
        $labels = [];
        $data = [];

        // Générer les 10 derniers mois
        for ($i = 9; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i months");
            $labels[] = $date->format('M');

            // Compter les interventions pour ce mois pour l'utilisateur
            $startDate = new \DateTime($date->format('Y-m-01'));
            $endDate = clone $startDate;
            $endDate->modify('last day of this month')->setTime(23, 59, 59);

            $count = $repository->createQueryBuilder('i')
                ->select('COUNT(i.id)')
                ->leftJoin('i.technicens', 't')
                ->where('t.id = :userId')
                ->andWhere('i.date_debut >= :start')
                ->andWhere('i.date_debut <= :end')
                ->setParameter('userId', $userId)
                ->setParameter('start', $startDate)
                ->setParameter('end', $endDate)
                ->getQuery()
                ->getSingleScalarResult();

            $data[] = (int) $count;
        }

        return [
            'labels' => $labels,
            'data' => $data
        ];
    }

    private function getActivitesRecentes(InterventionRepository $repository): array
    {
        // Récupère les 10 dernières interventions avec leurs techniciens
        $interventions = $repository->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->leftJoin('i.client', 'c')
            ->orderBy('i.date_debut', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $activites = [];
        foreach ($interventions as $intervention) {
            foreach ($intervention->getTechnicens() as $technicien) {
                $activites[] = [
                    'technicien' => $technicien->getPrenom() . ' ' . $technicien->getNom(),
                    'intervention' => $intervention->getLibelle(),
                    'client' => $intervention->getClient() ? $intervention->getClient()->getNom() : 'Non défini',
                    'date' => $intervention->getDateDebut(),
                    'status' => $intervention->getStatus(),
                ];
            }
        }

        // Trier par date décroissante
        usort($activites, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        // Retourner les 5 plus récentes
        return array_slice($activites, 0, 5);
    }
}
