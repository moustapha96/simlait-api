<?php

namespace App\Entity;


use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\TableCounterRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: TableCounterRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],
)]

class TableCounter
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["read", "write"])]
    private ?int $id = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private ?string $name = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["read", "write"])]
    private ?string $value = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $counter): self
    {
        $this->value = $counter;

        return $this;
    }
}
