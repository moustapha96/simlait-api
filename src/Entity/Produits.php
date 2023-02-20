<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProduitsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\InverseJoinColumn;
use Doctrine\ORM\Mapping\JoinTable;

#[ORM\Entity(repositoryClass: ProduitsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]
class Produits
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["read", "write"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true, unique: true)]
    #[Groups(["read", "write"])]
    private $nom;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    #[Groups(["read", "write"])]
    private $description;


    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $statut;

    #[ORM\ManyToMany(
        targetEntity: Conditionnements::class,
        inversedBy: "produits",
        cascade: ["persist"]
    )]

    #[Groups(["read", "write"])]
    private $conditionnements;

    #[ORM\ManyToMany(
        targetEntity: Profils::class,
        inversedBy: "produits",
        cascade: ["persist"]
    )]
    #[Groups(["read", "write"])]
    private $profils;
    


    #[ORM\Column(length: 255)]
    #[Groups(["read", "write"])]
    private ?string $unite = null;

    #[ORM\OneToMany(mappedBy: 'produits', targetEntity: UnitesDemande::class)]
    private Collection $unitesDemande;


    public function __construct()
    {
        $this->conditionnements = new ArrayCollection();
        $this->demandes = new ArrayCollection();
        $this->profils = new ArrayCollection();
        $this->unitesDemande = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        if ($nom == null) {
            $this->nom = '';
        } else {
            $this->nom = $nom;
        }
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }


    public function getStatut(): ?bool
    {
        return $this->statut;
    }

    public function setStatut(?bool $statut): self
    {
        $this->statut = $statut;
        return $this;
    }
     /**
     * @return Collection|Conditionnements[]
     */
    public function getConditionnements(): Collection
    {
        return $this->conditionnements;
    }

    public function addConditionnement(Conditionnements $conditionnement): self
    {
        if (!$this->conditionnements->contains($conditionnement)) {
            $this->conditionnements[] = $conditionnement;
        }

        return $this;
    }

    public function removeConditionnement(Conditionnements $conditionnement): self
    {
        $this->conditionnements->removeElement($conditionnement);

        return $this;
    }

    public function __toString()
    {
        return (string) $this->conditionnements;
    }

    // /**
    //  * @return Collection|TransformateurDemande[]
    //  */
    // public function getDemandes(): Collection
    // {
    //     return $this->demandes;
    // }

    // public function addDemande(TransformateurDemande $demande): self
    // {
    //     if (!$this->demandes->contains($demande)) {
    //         $this->demandes[] = $demande;
    //         $demande->setProduits($this);
    //     }

    //     return $this;
    // }

    // public function removeDemande(TransformateurDemande $demande): self
    // {
    //     if ($this->demandes->removeElement($demande)) {
    //         // set the owning side to null (unless already changed)
    //         if ($demande->getProduits() === $this) {
    //             $demande->setProduits(null);
    //         }
    //     }

    //     return $this;
    // }

    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->nom,
            'description' => $this->description,
            'statut' => $this->statut,
            'unite'=> $this->unite,
            'conditionnements' => $this->conditionnements,
        ];
    }
    public function asArray2(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->nom,
            'description' => $this->description,
            'unite'=> $this->unite,
            'statut' => $this->statut
        ];
    }
    public function asArrayCondi(): array
    {
        return [
            'id' => $this->getId(),
            'nom' => $this->nom,
            'description' => $this->description,
            'statut' => $this->statut,
            'unite'=> $this->unite,
            'conditionnements' => $this->getConditionnements() ,
        ];
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }

    /**
     * @return Collection<int, Profils>
     */
    public function getProfils(): Collection
    {
        return $this->profils;
    }

    public function addProfils(Profils $profil): self
    {
        if (!$this->profils->contains($profil)) {
            $this->profils->add($profil);
        }

        return $this;
    }

    public function removeProfils(Profils $profil): self
    {
        $this->profils->removeElement($profil);

        return $this;
    }


    public function getUnite(): ?string
    {
        return $this->unite;
    }

    public function setUnite(string $unite): self
    {
        $this->unite = $unite;

        return $this;
    }

    public function addProfil(Profils $profil): self
    {
        if (!$this->profils->contains($profil)) {
            $this->profils->add($profil);
        }

        return $this;
    }

    public function removeProfil(Profils $profil): self
    {
        $this->profils->removeElement($profil);

        return $this;
    }

    /**
     * @return Collection<int, UnitesDemande>
     */
    public function getUnitesDemande(): Collection
    {
        return $this->unitesDemande;
    }

    public function addUnitesDemande(UnitesDemande $unitesDemande): self
    {
        if (!$this->unitesDemande->contains($unitesDemande)) {
            $this->unitesDemande->add($unitesDemande);
            $unitesDemande->setProduits($this);
        }

        return $this;
    }

    public function removeUnitesDemande(UnitesDemande $unitesDemande): self
    {
        if ($this->unitesDemande->removeElement($unitesDemande)) {
            // set the owning side to null (unless already changed)
            if ($unitesDemande->getProduits() === $this) {
                $unitesDemande->setProduits(null);
            }
        }

        return $this;
    }
}
