<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UnitesDemandeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: UnitesDemandeRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]

#[ORM\Table(name: '`simlait_unite_demandes`')]
class UnitesDemande
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["read", "write"])]
    private ?int $id = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(["read", "write"])]
    private $dateDebut = null;

    #[ORM\Column(type: 'date', nullable: true)]
    #[Groups(["read", "write"])]
    private $dateFin = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $statut;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $besoin;

    #[ORM\ManyToOne(inversedBy: 'unitesDemandes')]
    #[Groups(["read", "write"])]
    private ?Unites $unites = null;

    #[ORM\OneToMany(mappedBy: 'unitesDemande', targetEntity: UnitesDemandeSuivi::class)]
    #[Groups(["read", "write"])]
    private Collection $unitesDemandeSuivis;

    #[ORM\ManyToOne(inversedBy: 'unitesDemande')]
    #[Groups(["read", "write"])]
    private ?Produits $produits = null;

    #[ORM\ManyToOne(inversedBy: 'unitesDemandes', cascade: ['persist'])]
    #[Groups(["read", "write"])]
    private ?UnitesAutre $unitesAutre = null;


    public function __construct()
    {

        $this->unitesDemandeSuivis = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;

        return $this;
    }



    public function getBesoin(): ?string
    {
        return $this->besoin;
    }

    public function setBesoin(string $besoin): self
    {
        $this->besoin = $besoin;

        return $this;
    }



    public function getUnites(): ?Unites
    {
        return $this->unites;
    }

    public function setUnites(?Unites $unites): self
    {
        $this->unites = $unites;

        return $this;
    }

    /**
     * @return Collection<int, UnitesDemandeSuivi>
     */
    public function getUnitesDemandeSuivis(): Collection
    {
        return $this->unitesDemandeSuivis;
    }

    public function addUnitesDemandeSuivi(UnitesDemandeSuivi $unitesDemandeSuivi): self
    {
        if (!$this->unitesDemandeSuivis->contains($unitesDemandeSuivi)) {
            $this->unitesDemandeSuivis->add($unitesDemandeSuivi);
            $unitesDemandeSuivi->setUnitesDemande($this);
        }

        return $this;
    }

    public function removeUnitesDemandeSuivi(UnitesDemandeSuivi $unitesDemandeSuivi): self
    {
        if ($this->unitesDemandeSuivis->removeElement($unitesDemandeSuivi)) {
            // set the owning side to null (unless already changed)
            if ($unitesDemandeSuivi->getUnitesDemande() === $this) {
                $unitesDemandeSuivi->setUnitesDemande(null);
            }
        }

        return $this;
    }

    public function getProduits(): ?Produits
    {
        return $this->produits;
    }

    public function setProduits(?Produits $produits): self
    {
        $this->produits = $produits;

        return $this;
    }


    public function getUnitesAutre(): ?UnitesAutre
    {
        return $this->unitesAutre;
    }

    public function setUnitesAutre(?UnitesAutre $unitesAutre): self
    {
        $this->unitesAutre = $unitesAutre;

        return $this;
    }
}