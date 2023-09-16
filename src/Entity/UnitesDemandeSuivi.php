<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UnitesDemandeSuiviRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UnitesDemandeSuiviRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],

)]

#[ORM\Table(name: '`simlait_unite_demande_suivis`')]
class UnitesDemandeSuivi
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["read", "write"])]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime')]
    #[Groups(["read", "write"])]
    private $date;

    #[ORM\Column(type: 'text')]
    #[Groups(["read", "write"])]
    private $message;

    #[ORM\Column(type: 'text')]
    #[Groups(["read", "write"])]
    private $observation;

    #[ORM\ManyToOne(targetEntity: Unites::class)]
    #[Groups(["read", "write"])]
    private $unites;

    #[ORM\ManyToOne(targetEntity: UnitesAutre::class ,  )   ]
    #[Groups(["read", "write"])]
    private $unitesAutre = null;


    #[ORM\ManyToOne(inversedBy: 'unitesDemandeSuivis')]
    #[Groups(["read", "write"])]
    private ?UnitesDemande $unitesDemande = null;


    public function __construct()
    {

    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }


    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getObservation(): ?string
    {
        return $this->observation;
    }

    public function setObservation(string $observation): self
    {
        $this->observation = $observation;
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

    public function getUnitesDemande(): ?UnitesDemande
    {
        return $this->unitesDemande;
    }

    public function setUnitesDemande(?UnitesDemande $unitesDemande): self
    {
        $this->unitesDemande = $unitesDemande;

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