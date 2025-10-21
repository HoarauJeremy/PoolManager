<?php

namespace App\Controller;

use App\Entity\Intervention;
use App\Enum\Status;
use App\Repository\InterventionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(InterventionRepository $interventionRepository): Response
    {
        // Récupération des interventions depuis la base de données
        $interventions = $interventionRepository->findBy([], ['date_debut' => 'DESC'], 10);

        // Calcul des statistiques par statut
        $statsParStatut = [
            'planifiees' => $interventionRepository->count(['status' => Status::PLANIFIER]),
            'en_cours' => $interventionRepository->count(['status' => Status::ENCOURS]),
            'terminees' => $interventionRepository->count(['status' => Status::TERMINER]),
            'annulees' => $interventionRepository->count(['status' => Status::ANNULER])
        ];

        // Calcul des interventions par mois (12 derniers mois)
        $interventionsParMois = $this->getInterventionsParMois($interventionRepository);

        // Calcul des interventions par type
        $interventionsParType = $this->getInterventionsParType($interventionRepository);

        // Statistiques pour les graphiques
        $stats = [
            'interventions_par_mois' => $interventionsParMois,
            'interventions_par_type' => $interventionsParType,
            'interventions_par_statut' => $statsParStatut
        ];

        return $this->render('dashboard/index.html.twig', [
            'interventions' => $interventions,
            'stats' => $stats,
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
}
