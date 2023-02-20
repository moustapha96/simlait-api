<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\EmballageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: EmballageRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]
class Emballage
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["read", "write"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $nom;

    #[ORM\Column(type: 'text', nullable: true)]
    #[Groups(["read", "write"])]
    private $description;


    #[ORM\Column(type: 'boolean')] 
    #[Groups(["read", "write"])]
    private $statut;



    public function __construct()
    {
        $this->conditionnements = new ArrayCollection();
        $this->collectes = new ArrayCollection();
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

    public function setStatut(bool $statut): self
    {
        $this->statut = $statut;

        return $this;
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
