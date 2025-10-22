<?php

namespace App\Entity;

use App\Repository\TypeInterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// Indique à Doctrine que cette classe représente une entité de base de données
// et précise le repository associé pour les requêtes personnalisées.
#[ORM\Entity(repositoryClass: TypeInterventionRepository::class)]
class TypeIntervention
{
    /**
     * Identifiant unique du type d’intervention (clé primaire).
     * Doctrine gère automatiquement la génération de cet ID (auto-incrément).
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Nom du type d’intervention (ex: "Nettoyage", "Maintenance", etc.)
     * Champ de type chaîne de caractères, obligatoire (non nul).
     */
    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    /**
     * Relation "OneToMany" :
     * - Un type d’intervention peut être associé à plusieurs interventions.
     * - L’attribut `mappedBy` indique que la propriété "type" dans l’entité Intervention
     *   est le côté propriétaire de la relation.
     * - `orphanRemoval: true` signifie que si on retire une Intervention de la collection
     *   et qu’elle n’est plus liée à aucun type, Doctrine la supprimera en base.
     *
     * @var Collection<int, Intervention>  Une collection Doctrine (semblable à un tableau)
     */
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'type', orphanRemoval: true)]
    private Collection $interventions;

    /**
     * Le constructeur initialise toujours les collections Doctrine avec ArrayCollection.
     * Cela évite les erreurs du type "null has no method add()" si on manipule les relations.
     */
    public function __construct()
    {
        $this->interventions = new ArrayCollection();
    }

    // ────────────────────────────
    //   GETTERS & SETTERS
    // ────────────────────────────

    /**
     * Retourne l'identifiant unique du type d’intervention.
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom du type d’intervention.
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * Définit le nom du type d’intervention.
     * Retourne l’objet courant pour permettre le chaînage (ex: $type->setNom('Test')->setAutreChose())
     */
    public function setNom(string $nom): static
    {
        $this->nom = $nom;
        return $this;
    }

    /**
     * Retourne la collection d’interventions associées à ce type.
     *
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    /**
     * Ajoute une intervention à ce type.
     * Vérifie d’abord qu’elle n’est pas déjà présente avant de l’ajouter.
     * Met à jour la relation du côté Intervention (`setType()`).
     */
    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setType($this); // Synchronisation côté propriétaire
        }

        return $this;
    }

    /**
     * Supprime une intervention de ce type.
     * Si elle était liée à ce type, on coupe aussi le lien côté Intervention.
     */
    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // Si l’intervention faisait bien référence à ce type, on la dissocie
            if ($intervention->getType() === $this) {
                $intervention->setType(null);
            }
        }

        return $this;
    }
}
