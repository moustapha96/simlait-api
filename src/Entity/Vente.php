<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\VenteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: VenteRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]
class Vente
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["read", "write"])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Produits::class)]
    #[Groups(["read", "write"])]
    private $produits;

    #[ORM\ManyToOne(targetEntity: Conditionnements::class)]
    #[Groups(["read", "write"])]
    private $conditionnements;

    #[ORM\ManyToOne(targetEntity: Unites::class)]
    #[Groups(["read", "write"])]
    private $unites;

    #[ORM\ManyToOne(targetEntity: UserMobile::class, inversedBy: 'ventes')]
    #[Groups(["read", "write"])]
    private $user;

    #[ORM\ManyToOne(targetEntity: Emballage::class)]
    #[Groups(["read", "write"])]
    private $emballages;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(["read", "write"])]
    private $dateVente;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isSynchrone;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isCertified;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(["read", "write"])]
    private $quantite;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(["read", "write"])]
    private $prix;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isDeleted;


    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUser(): ?UserMobile
    {
        return $this->user;
    }

    public function setUser(?UserMobile $user): self
    {
        $this->user = $user;
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



    public function getConditionnements(): ?Conditionnements
    {
        return $this->conditionnements;
    }

    public function setConditionnements(?Conditionnements $conditionnements): self
    {
        $this->conditionnements = $conditionnements;
        return $this;
    }

    public function getEmballages(): ?Emballage
    {
        return $this->emballages;
    }

    public function setEmballages(?Emballage $emballages): self
    {
        $this->emballages = $emballages;

        return $this;
    }

    public function getDateVente(): ?\DateTimeInterface
    {
        return $this->dateVente;
    }

    public function setDateVente(?\DateTimeInterface $dateVente): self
    {
        $this->dateVente = $dateVente;

        return $this;
    }

    public function getIsSynchrone(): ?bool
    {
        return $this->isSynchrone;
    }

    public function setIsSynchrone(?bool $isSynchrone): self
    {
        $this->isSynchrone = $isSynchrone;

        return $this;
    }

    public function getIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function setIsCertified(?bool $isCertified): self
    {
        $this->isCertified = $isCertified;

        return $this;
    }

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;
        if ($this->isDeleted == true) {
            $this->isSynchrone = false;
            $this->isCertified = false;
        }

        return $this;
    }


    public function asArray(): array
    {
        return [
            'id'=> $this->getId(),
            'produits' => $this->produits->asArray(),
            'conditionnements' => $this->conditionnements->asArray(),
            'unites' => $this->unites->asArray(),
            'user' => $this->user->asArray(),
            'emballages' => $this->emballages->asArray(),
            'dateVente' => $this->dateVente,
            'isSynchrone' => $this->isSynchrone,
            'isCertified' => $this->isCertified,
            'quantite' =>$this->quantite,
            'prix'=>$this->prix ,
            'isDeleted'=> $this->isDeleted
        ];
    }
    public function asArray2(): array
    {
        return [
            'id'=> $this->getId(),
            'produits' => $this->produits->asArray(),
            'conditionnements' => $this->conditionnements->asArray(),
            'unites' => $this->unites->asArray(),
            'user' => $this->user,
            'emballages' => $this->emballages->asArray(),
            'dateVente' => $this->dateVente,
            'isSynchrone' => $this->isSynchrone,
            'isCertified' => $this->isCertified,
            'quantite' =>$this->quantite,
            'prix'=>$this->prix ,
            'isDeleted'=> $this->isDeleted
        ];
    }

    public function isIsSynchrone(): ?bool
    {
        return $this->isSynchrone;
    }

    public function isIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
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


}
