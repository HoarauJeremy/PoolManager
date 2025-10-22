<?php

namespace App\Repository;

use App\Entity\Client;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Classe ClientRepository
 * 
 * Cette classe étend ServiceEntityRepository et fournit des méthodes pour interagir 
 * avec les entités Client en base de données.
 * 
 * @extends ServiceEntityRepository<Client>
 */
class ClientRepository extends ServiceEntityRepository
{
    /**
     * Constructeur de la classe
     * 
     * @param ManagerRegistry $registry Le service ManagerRegistry
     */
    public function __construct(ManagerRegistry $registry)
    {
        // Appel au constructeur parent avec l'entité Client
        parent::__construct($registry, Client::class);
    }

    // Les méthodes suivantes sont commentées par défaut mais peuvent être utiles :

    /*
     * Trouve des clients selon un champ exemple
     * 
     * @param mixed $value La valeur à rechercher
     * @return Client[] Retourne un tableau d'objets Client
     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')  // Condition WHERE
//            ->setParameter('val', $value)         // Définit la valeur du paramètre
//            ->orderBy('c.id', 'ASC')             // Tri par id croissant
//            ->setMaxResults(10)                   // Limite à 10 résultats
//            ->getQuery()                          // Crée la requête
//            ->getResult()                         // Exécute et retourne un tableau de résultats
//        ;
//    }

    /*
     * Trouve un client selon un champ exemple
     * 
     * @param mixed $value La valeur à rechercher
     * @return Client|null Retourne un objet Client ou null
     */
//    public function findOneBySomeField($value): ?Client
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')  // Condition WHERE
//            ->setParameter('val', $value)         // Définit la valeur du paramètre
//            ->getQuery()                          // Crée la requête
//            ->getOneOrNullResult()                // Exécute et retourne un seul résultat ou null
//        ;
//    }
}