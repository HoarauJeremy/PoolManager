<?php

namespace App\Repository;

use App\Entity\Intervention;
use App\Enum\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Intervention>
 */
class InterventionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Intervention::class);
    }

    /**
     * Récupère les interventions avec pagination et filtre optionnel par statut
     */
    public function findPaginatedWithFilter(int $page, int $limit, ?Status $status = null): Query
    {
        $qb = $this->createQueryBuilder('i');

        if ($status !== null) {
            $qb->where('i.status = :status')
                ->setParameter('status', $status);
        }

        return $qb
            ->orderBy('i.date_debut', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();
    }

    /**
     * Récupère les interventions à venir pour un utilisateur
     */
    public function findUpcomingByUser(int $userId, int $limit = 5): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->where('t.id = :userId')
            ->andWhere('i.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', Status::PLANIFIER)
            ->orderBy('i.date_debut', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère les interventions en cours pour un utilisateur
     */
    public function findInProgressByUser(int $userId): array
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->where('t.id = :userId')
            ->andWhere('i.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', Status::ENCOURS)
            ->orderBy('i.date_debut', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Récupère l'historique (terminées et annulées) pour un utilisateur avec pagination
     */
    public function findHistoryByUser(int $userId, int $page, int $limit): Query
    {
        return $this->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->where('t.id = :userId')
            ->andWhere('i.status IN (:statuses)')
            ->setParameter('userId', $userId)
            ->setParameter('statuses', [Status::TERMINER, Status::ANNULER])
            ->orderBy('i.date_debut', 'DESC')
            ->setFirstResult(($page - 1) * $limit)
            ->setMaxResults($limit)
            ->getQuery();
    }

    /**
     * Compte les interventions par statut pour un utilisateur
     */
    public function countByUserAndStatus(int $userId, Status $status): int
    {
        return (int) $this->createQueryBuilder('i')
            ->select('COUNT(i.id)')
            ->leftJoin('i.technicens', 't')
            ->where('t.id = :userId')
            ->andWhere('i.status = :status')
            ->setParameter('userId', $userId)
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Récupère le nombre d'interventions par mois sur les 10 derniers mois
     */
    public function getInterventionsPerMonth(): array
    {
        $labels = [];
        $data = [];

        for ($i = 9; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i months");
            $labels[] = $date->format('M');

            $startDate = new \DateTime($date->format('Y-m-01'));
            $endDate = clone $startDate;
            $endDate->modify('last day of this month')->setTime(23, 59, 59);

            $count = $this->createQueryBuilder('i')
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

    /**
     * Récupère le nombre d'interventions par type
     */
    public function getInterventionsPerType(): array
    {
        $results = $this->createQueryBuilder('i')
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

    /**
     * Récupère le nombre d'interventions par mois pour un utilisateur
     */
    public function getInterventionsPerMonthByUser(int $userId): array
    {
        $labels = [];
        $data = [];

        for ($i = 9; $i >= 0; $i--) {
            $date = new \DateTime();
            $date->modify("-$i months");
            $labels[] = $date->format('M');

            $startDate = new \DateTime($date->format('Y-m-01'));
            $endDate = clone $startDate;
            $endDate->modify('last day of this month')->setTime(23, 59, 59);

            $count = $this->createQueryBuilder('i')
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

    /**
     * Récupère les activités récentes (5 dernières)
     */
    public function getRecentActivities(int $limit = 5): array
    {
        $interventions = $this->createQueryBuilder('i')
            ->leftJoin('i.technicens', 't')
            ->leftJoin('i.client', 'c')
            ->orderBy('i.date_debut', 'DESC')
            ->setMaxResults($limit * 2)
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

        usort($activites, function($a, $b) {
            return $b['date'] <=> $a['date'];
        });

        return array_slice($activites, 0, $limit);
    }
}
