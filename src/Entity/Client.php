<?php

// Déclaration de l'espace de noms de l'entité
namespace App\Entity;

// Import des classes nécessaires
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

// Déclaration de la classe Client comme entité ORM
#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client
{
    // Déclaration des propriétés

    #[ORM\Id] // Clé primaire
    #[ORM\GeneratedValue] // Valeur générée automatiquement
    #[ORM\Column] // Colonne de base de données
    private ?int $id = null;

    #[ORM\Column(length: 255)] // Colonne de type varchar(255)
    private ?string $nom = null; // Nom du client

    #[ORM\Column(length: 255)]
    private ?string $prenom = null; // Prénom du client

    #[ORM\Column(length: 255)]
    private ?string $email = null; // Adresse email du client

    #[ORM\Column(length: 255)]
    private ?string $adresse = null; // Adresse postale

    #[ORM\Column(length: 255)]
    private ?string $ville = null; // Ville de résidence

    #[ORM\Column]
    private ?int $code_postal = null; // Code postal

    #[ORM\Column(nullable: true)] // Peut être null
    private ?int $tel_fixe = null; // Téléphone fixe

    #[ORM\Column]
    private ?int $tel_gsm = null; // Téléphone mobile

    // Déclaration de la relation OneToMany avec l'entité Intervention
    // Un client peut avoir plusieurs interventions
    /**
     * @var Collection<int, Intervention>
     */
    #[ORM\OneToMany(targetEntity: Intervention::class, mappedBy: 'client')]
    private Collection $interventions;

    // Constructeur initialisant la collection d'interventions
    public function __construct()
    {
        $this->interventions = new ArrayCollection();
    }

    // =====================
    // GETTERS ET SETTERS
    // =====================

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): static
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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

    public function getTelFixe(): ?int
    {
        return $this->tel_fixe;
    }

    public function setTelFixe(?int $tel_fixe): static
    {
        $this->tel_fixe = $tel_fixe;

        return $this;
    }

    public function getTelGsm(): ?int
    {
        return $this->tel_gsm;
    }

    public function setTelGsm(int $tel_gsm): static
    {
        $this->tel_gsm = $tel_gsm;

        return $this;
    }

    // Méthode pour récupérer la collection d'interventions du client
    /**
     * @return Collection<int, Intervention>
     */
    public function getInterventions(): Collection
    {
        return $this->interventions;
    }

    // Méthode pour ajouter une intervention à ce client
    public function addIntervention(Intervention $intervention): static
    {
        if (!$this->interventions->contains($intervention)) {
            $this->interventions->add($intervention);
            $intervention->setClient($this);
        }

        return $this;
    }

    // Méthode pour supprimer une intervention de ce client
    public function removeIntervention(Intervention $intervention): static
    {
        if ($this->interventions->removeElement($intervention)) {
            // Si l'intervention était bien associée à ce client, on enlève la référence
            if ($intervention->getClient() === $this) {
                $intervention->setClient(null);
            }
        }

        return $this;
    }
}