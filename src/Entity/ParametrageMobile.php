<?php

namespace App\Entity;

use App\Repository\ParametrageMobileRepository;
use Doctrine\ORM\Mapping as ORM;

use ApiPlatform\Core\Annotation\ApiResource;

#[ApiResource]

#[ORM\Table(name: '`simlait_parametrage_mobiles`')]
#[ORM\Entity(repositoryClass: ParametrageMobileRepository::class)]
class ParametrageMobile
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $urlProd = null;

    #[ORM\Column(length: 255)]
    private ?string $urlDemo = null;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(length: 255)]
    private ?string $contact = null;

    #[ORM\Column]
    private ?bool $hasNotification = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contactSupport = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $emailSupport = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUrlProd(): ?string
    {
        return $this->urlProd;
    }

    public function setUrlProd(string $urlProd): self
    {
        $this->urlProd = $urlProd;

        return $this;
    }

    public function getUrlDemo(): ?string
    {
        return $this->urlDemo;
    }

    public function setUrlDemo(string $urlDemo): self
    {
        $this->urlDemo = $urlDemo;

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

    public function getContact(): ?string
    {
        return $this->contact;
    }

    public function setContact(string $contact): self
    {
        $this->contact = $contact;

        return $this;
    }

    public function isHasNotification(): ?bool
    {
        return $this->hasNotification;
    }

    public function setHasNotification(bool $hasNotification): self
    {
        $this->hasNotification = $hasNotification;

        return $this;
    }

    public function getContactSupport(): ?string
    {
        return $this->contactSupport;
    }

    public function setContactSupport(?string $contactSupport): self
    {
        $this->contactSupport = $contactSupport;

        return $this;
    }

    public function getEmailSupport(): ?string
    {
        return $this->emailSupport;
    }

    public function setEmailSupport(?string $emailSupport): self
    {
        $this->emailSupport = $emailSupport;

        return $this;
    }
}
