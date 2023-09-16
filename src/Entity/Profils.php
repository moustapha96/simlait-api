<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ProfilsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ProfilsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]

#[ORM\Table(name: '`simlait_profils`')]
class Profils
{

    #[ORM\Id]
    #[ORM\Column]
    #[Groups(["write", "read"])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(["write", "read"])]
    private ?string $nom = null;

    #[ORM\ManyToMany(
        targetEntity: Produits::class,
        mappedBy: 'profils',
        cascade: ["persist"],
    )]
    #[Groups(["write"])]
    private $produits;

    #[ORM\OneToMany(mappedBy: 'profil', targetEntity: UserMobile::class)]
    #[Groups(["write"])]
    private Collection $userMobile;


    #[ORM\Column(length: 255)]
    #[Groups(["write", "read"])]
    private ?string $denomination = null;

    #[ORM\OneToMany(mappedBy: 'profil', targetEntity: Unites::class)]
    //    #[Groups(["write","read"])]
    private Collection $unites;

    #[ORM\Column(nullable: true)]
    #[Groups(["write", "read"])]
    private ?bool $status = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["write", "read"])]
    private ?int $quantite = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["write", "read"])]
    private ?string $periode = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["write", "read"])]
    private ?string $indicatif = null;

    public function __construct()
    {

        $this->produits = new ArrayCollection();
        $this->userMobile = new ArrayCollection();
        $this->unites = new ArrayCollection();
    }


    public function __toString()
    {
        return sprintf('Profil #%d', $this->getId());
    }

    public function setid(int $id): self
    {
        $this->id = $id;
        return $this;
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
        $this->nom = $nom;
        return $this;
    }

    /**
     * @return Collection<int, Produits>
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produits $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits->add($produit);
            $produit->addProfil($this);
        }
        return $this;
    }

    public function removeProduit(Produits $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            $produit->removeProfil($this);
        }
        return $this;
    }

    /**
     * @return Collection<int, UserMobile>
     */
    public function getUserMobile(): Collection
    {
        return $this->userMobile;
    }

    public function addUserMobile(UserMobile $userMobile): self
    {
        if (!$this->userMobile->contains($userMobile)) {
            $this->userMobile->add($userMobile);
            $userMobile->setProfil($this);
        }

        return $this;
    }

    public function removeUserMobile(UserMobile $userMobile): self
    {
        if ($this->userMobile->removeElement($userMobile)) {
            // set the owning side to null (unless already changed)
            if ($userMobile->getProfil() === $this) {
                $userMobile->setProfil(null);
            }
        }

        return $this;
    }


    public function asArray(): array
    {
        return [
            'id' => $this->id,
            'nom' => $this->nom,
            'denomination' => $this->denomination,
            'status' => $this->status,
            'quantite' => $this->quantite,
            'indicatif' => $this->indicatif
        ];
    }

    public function getDenomination(): ?string
    {
        return $this->denomination;
    }

    public function setDenomination(string $denomination): self
    {
        $this->denomination = $denomination;

        return $this;
    }


    /**
     * @return Collection<int, Unites>
     */
    public function getUnites(): Collection
    {
        return $this->unites;
    }

    public function addUnite(Unites $unite): self
    {
        if (!$this->unites->contains($unite)) {
            $this->unites->add($unite);
            $unite->setProfil($this);
        }

        return $this;
    }

    public function removeUnite(Unites $unite): self
    {
        if ($this->unites->removeElement($unite)) {
            // set the owning side to null (unless already changed)
            if ($unite->getProfil() === $this) {
                $unite->setProfil(null);
            }
        }

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(?bool $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getQuantite(): ?int
    {
        return $this->quantite;
    }

    public function setQuantite(?int $quantite): self
    {
        $this->quantite = $quantite;
        return $this;
    }

    public function getPeriode(): ?string
    {
        return $this->periode;
    }

    public function setPeriode(?string $periode): self
    {
        $this->periode = $periode;

        return $this;
    }

    public function getIndicatif(): ?string
    {
        return $this->indicatif;
    }

    public function setIndicatif(?string $indicatif): self
    {
        $this->indicatif = $indicatif;

        return $this;
    }
}
