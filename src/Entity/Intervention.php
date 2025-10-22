<?php

namespace App\Entity;

use App\Enum\Status;
use App\Repository\InterventionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * Représente une intervention dans le système.
 *
 * Une intervention correspond à une opération réalisée pour un client :
 * installation, maintenance, réparation, etc.
 * Elle est associée à un type, un client, des techniciens et éventuellement du matériel.
 */
#[ORM\Entity(repositoryClass: InterventionRepository::class)]
class Intervention
{
    // ==============================
    // 🆔 Identifiant unique
    // ==============================

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null; // Identifiant auto-incrémenté de l'intervention


    // ==============================
    // 📋 Informations générales
    // ==============================

    #[ORM\Column(length: 255)]
    private ?string $libelle = null; // Nom ou titre de l’intervention

    #[ORM\Column]
    private ?\DateTimeImmutable $date_debut = null; // Date et heure de début prévue

    #[ORM\Column]
    private ?\DateTimeImmutable $date_fin = null; // Date et heure de fin prévue

    #[ORM\Column(length: 255)]
    private ?string $adresse = null; // Adresse où se déroule l’intervention

    #[ORM\Column(length: 255)]
    private ?string $ville = null; // Ville de l’intervention

    #[ORM\Column]
    private ?int $code_postal = null; // Code postal du lieu d’intervention

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $infos = null; // Informations complémentaires (commentaires, remarques, etc.)


    // ==============================
    // ⚙️ Statut et relations
    // ==============================

    #[ORM\Column(enumType: Status::class)]
    private ?Status $status = null; // Statut actuel de l’intervention (PLANIFIER, ENCOURS, TERMINEE, etc.)

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?TypeIntervention $type = null; // Type d’intervention (maintenance, installation, etc.)


    // ==============================
    // 🔧 Relation avec le matériel
    // ==============================

    /**
     * @var Collection<int, Materiel> Liste des matériels utilisés ou concernés par cette intervention
     */
    #[ORM\ManyToMany(targetEntity: Materiel::class, inversedBy: 'interventions')]
    private Collection $materiel;


    // ==============================
    // 👤 Relation avec le client
    // ==============================

    #[ORM\ManyToOne(inversedBy: 'interventions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null; // Client pour lequel l’intervention est réalisée


    // ==============================
    // 👷 Relation avec les techniciens
    // ==============================

    /**
     * @var Collection<int, User> Liste des techniciens affectés à l’intervention
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'interventions')]
    private Collection $techniciens;


    // ==============================
    // 🔨 Constructeur
    // ==============================

    public function __construct()
    {
        // Initialise les collections pour éviter les erreurs null
        $this->materiel = new ArrayCollection();
        $this->techniciens = new ArrayCollection();
    }


    // ==============================
    // 🧩 Getters / Setters (Accesseurs)
    // ==============================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLibelle(): ?string
    {
        return $this->libelle;
    }

    public function setLibelle(string $libelle): static
    {
        $this->libelle = $libelle;
        return $this;
    }

    public function getDateDebut(): ?\DateTimeImmutable
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeImmutable $date_debut): static
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDateFin(): ?\DateTimeImmutable
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeImmutable $date_fin): static
    {
        $this->date_fin = $date_fin;
        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;
        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): static
    {
        $this->ville = $ville;
        return $this;
    }

    public function getCodePostal(): ?int
    {
        return $this->code_postal;
    }

    public function setCodePostal(int $code_postal): static
    {
        $this->code_postal = $code_postal;
        return $this;
    }

    public function getInfos(): ?string
    {
        return $this->infos;
    }

    public function setInfos(?string $infos): static
    {
        $this->infos = $infos;
        return $this;
    }

    public function getStatus(): ?Status
    {
        return $this->status;
    }

    public function setStatus(Status $status): static
    {
        $this->status = $status;
        return $this;
    }

    public function getType(): ?TypeIntervention
    {
        return $this->type;
    }

    public function setType(?TypeIntervention $type): static
    {
        $this->type = $type;
        return $this;
    }


    // ==============================
    // 🔧 Gestion du matériel
    // ==============================

    /**
     * Retourne la liste des matériels liés à l’intervention.
     *
     * @return Collection<int, Materiel>
     */
    public function getMateriel(): Collection
    {
        return $this->materiel;
    }

    /**
     * Ajoute un matériel à l’intervention.
     */
    public function addMateriel(Materiel $materiel): static
    {
        if (!$this->materiel->contains($materiel)) {
            $this->materiel->add($materiel);
        }
        return $this;
    }

    /**
     * Supprime un matériel lié à l’intervention.
     */
    public function removeMateriel(Materiel $materiel): static
    {
        $this->materiel->removeElement($materiel);
        return $this;
    }


    // ==============================
    // 👤 Gestion du client
    // ==============================

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;
        return $this;
    }


    // ==============================
    // 👷 Gestion des techniciens
    // ==============================

    /**
     * Retourne la liste des techniciens affectés à l’intervention.
     *
     * @return Collection<int, User>
     */
    public function getTechniciens(): Collection
    {
        return $this->techniciens;
    }

    /**
     * Ajoute un technicien à l’intervention.
     */
    public function addTechnicien(User $technicien): static
    {
        if (!$this->techniciens->contains($technicien)) {
            $this->techniciens->add($technicien);
        }
        return $this;
    }

    /**
     * Retire un technicien de l’intervention.
     */
    public function removeTechnicien(User $technicien): static
    {
        $this->techniciens->removeElement($technicien);
        return $this;
    }
}
