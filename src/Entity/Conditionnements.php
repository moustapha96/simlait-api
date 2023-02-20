<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ConditionnementsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: ConditionnementsRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]

class Conditionnements
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["read", "write"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private $nom;

    #[ORM\Column(type: 'string', length: 200, nullable: true)]
    #[Groups(["read", "write"])]
    private $description;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $statut;

    #[ORM\ManyToMany(
        targetEntity: Produits::class,
        mappedBy: 'conditionnements',
        cascade: ["persist"],
    )]
    #[Groups(["write"])]
    private $produits;



    public function __construct()
    {
        $this->produits = new ArrayCollection();
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
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
     * @return Collection|Produits[]
     */
    public function getProduits(): Collection
    {
        return $this->produits;
    }

    public function addProduit(Produits $produit): self
    {
        if (!$this->produits->contains($produit)) {
            $this->produits[] = $produit;
            $produit->addConditionnement($this);
        }

        return $this;
    }

    public function removeProduit(Produits $produit): self
    {
        if ($this->produits->removeElement($produit)) {
            $produit->removeConditionnement($this);
        }

        return $this;
    }

    public function __toString()
    {
        return (string) $this->produits;
    }
   
    public function asArray(): array
    {
        return [
            'id'=> $this->getId(),
            'nom' => $this->nom,
            'description' => $this->description,
            'statut' => $this->statut,
        ];
    }

    public function isStatut(): ?bool
    {
        return $this->statut;
    }
}
