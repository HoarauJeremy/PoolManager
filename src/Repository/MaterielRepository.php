<?php

namespace App\Repository;

use App\Entity\Materiel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entité Materiel
 * 
 * Ce repository fournit des méthodes personnalisées pour interroger
 * la base de données concernant les matériels. Il étend ServiceEntityRepository
 * pour bénéficier des méthodes CRUD de base de Doctrine.
 * 
 * @extends ServiceEntityRepository<Materiel>
 */
class MaterielRepository extends ServiceEntityRepository
{
    /**
     * Constructeur du repository
     * 
     * Initialise le repository avec le registre de gestionnaires
     * et l'entité Materiel associée.
     * 
     * @param ManagerRegistry $registry Registre des gestionnaires de persistance
     */
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Materiel::class);
    }

    /**
     * Recherche des matériels par libellé
     * 
     * Cette méthode permet de rechercher des matériels dont le libellé
     * contient le terme recherché (recherche partielle).
     * 
     * @param string $searchTerm Terme de recherche
     * @return array Liste des matériels correspondants
     */
    public function findByLibelle(string $searchTerm): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.libelle LIKE :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%')
            ->orderBy('m.libelle', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche des matériels avec stock faible
     * 
     * Cette méthode retourne tous les matériels dont la quantité
     * est inférieure ou égale au seuil spécifié.
     * 
     * @param int $threshold Seuil de stock minimum
     * @return array Liste des matériels avec stock faible
     */
    public function findLowStock(int $threshold = 5): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.quantite <= :threshold')
            ->setParameter('threshold', $threshold)
            ->orderBy('m.quantite', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Compte le nombre total de matériels
     * 
     * @return int Nombre total de matériels en base
     */
    public function countTotal(): int
    {
        return $this->createQueryBuilder('m')
            ->select('COUNT(m.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
