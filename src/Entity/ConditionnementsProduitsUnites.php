<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\ConditionnementsProduitsUnitesRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConditionnementsProduitsUnitesRepository::class)]
#[ApiResource]
class ConditionnementsProduitsUnites
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Produits::class)]
    private $produits;

    #[ORM\ManyToOne(targetEntity: Conditionnements::class)]
    private $conditionnements;

    #[ORM\Column(type: 'datetime_immutable')]
    private $createdAt;

    #[ORM\ManyToOne(targetEntity: Unites::class )]
    #[ORM\JoinColumn(nullable: false)]
    private $unites;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

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




}
