<?php

namespace App\Entity;

use App\Repository\UnitesUserRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UnitesUserRepository::class)]
class UnitesUser
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[Groups(["read", "write"])]
    private ?Unites $unites = null;

    #[ORM\ManyToOne]
    #[Groups(["read", "write"])]
    private ?UserMobile $userMobile = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUserMobile(): ?UserMobile
    {
        return $this->userMobile;
    }

    public function setUserMobile(?UserMobile $userMobile): self
    {
        $this->userMobile = $userMobile;

        return $this;
    }

    public function asArray(): array
    {

        return [
            'unites' => $this->unites->asArray(),
            'userMobile'=> $this->userMobile->asArray()

        ];
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
