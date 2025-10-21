<?php

namespace App\Entity;

use App\Repository\MaterielRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité Materiel
 * 
 * Cette classe représente un matériel dans le système de gestion de piscine.
 * Un matériel peut être associé à plusieurs interventions et contient
 * des informations sur son libellé, sa quantité et sa description.
 * 
 */
#[ORM\Entity(repositoryClass: MaterielRepository::class)]
class Materiel
{
    /**
     * Identifiant unique du matériel
     * 
     * @var int|null Identifiant auto-généré par la base de données
     */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /**
     * Libellé du matériel
     * 
     * @var string|null Nom ou désignation du matériel (ex: "Chlore", "Filtre à sable")
     */
    #[ORM\Column(length: 255)]
    private ?string $libelle = null;

    /**
     * Quantité disponible du matériel
     * 
     * @var int|null Nombre d'unités disponibles en stock
     */
    #[ORM\Column]
    private ?int $quantite = null;

    /**
     * Description détaillée du matériel
     * 
     * @var string|null Description complète du matériel, son usage, ses caractéristiques
     */
    #[ORM\Column(type: Types::TEXT)]
    private ?string $description = null;

    /**
     * Collection des interventions utilisant ce matériel
     * 
     * @var Collection<int, Intervention> Liste des interventions associées à ce matériel
     */
    #[ORM\ManyToMany(targetEntity: Intervention::class, mappedBy: 'materiel')]
    private Collection $interventions;

    /**
     * Constructeur de l'entité Materiel
     * 
     * Initialise la collection des interventions associées au matériel
     */
    public function __construct()
    {
        $this->interventions = new ArrayCollection();
    }

    /**
     * Récupère l'identifiant unique du matériel
     * 
     * @return int|null L'identifiant du matériel ou null si non persisté
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Récupère le libellé du matériel
     * 
     * @return string|null Le libellé du matériel
     */
    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    /**
     * Définit le libellé du matériel
     * 
     * @param string $libelle Le nouveau libellé du matériel
     * @return static Retourne l'instance courante pour le chaînage de méthodes
     */
    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;

        return $this;
    }

    /**
     * Récupère la quantité disponible du matériel
     * 
     * @return int|null La quantité disponible en stock
     */
    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    /**
     * Définit la quantité disponible du matériel
     * 
     * @param int $quantite La nouvelle quantité en stock
     * @return static Retourne l'instance courante pour le chaînage de méthodes
     */
    public function setQuantite(int $quantite): static
    {
        $this->quantite = $quantite;

        return $this;
    }

    /**
     * Récupère la description du matériel
     * 
     * @return string|null La description détaillée du matériel
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Définit la description du matériel
     * 
     * @param string $description La nouvelle description du matériel
     * @return static Retourne l'instance courante pour le chaînage de méthodes
     */
    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Récupère la collection des interventions utilisant ce matériel
     * 
     * @return Collection<int, Intervention> Collection des interventions associées
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    /**
     * Ajoute une intervention à la liste des interventions utilisant ce matériel
     * 
     * @param Intervention $intervention L'intervention à ajouter
     * @return static Retourne l'instance courante pour le chaînage de méthodes
     */
    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->addMateriel($this);
        }

        return $this;
    }

    /**
     * Retire une intervention de la liste des interventions utilisant ce matériel
     * 
     * @param Intervention $intervention L'intervention à retirer
     * @return static Retourne l'instance courante pour le chaînage de méthodes
     */
    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            $intervention->removeMateriel($this);
        }

        return $this;
    }
}
