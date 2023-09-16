<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;


#[UniqueEntity(fields: ['email'], message: 'Cet e-mail est déjà utilisé par un autre utilisateur')]
#[UniqueEntity(fields: ['phone'], message: 'Ce numéro de téléphone est déjà utilisé')]
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[
    ApiResource(
        normalizationContext: ['groups' => ['read']],
        denormalizationContext: ['groups' => ['write']],
        order: ['id' => 'DESC'],
    )
]

#[ORM\Table(name: '`simlait_users`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["read", "write"])]
    private $id;

    #[ORM\Column(type: 'string', nullable: true, length: 255, unique: true)]
    // #[Assert\NotBlank()]
    #[Groups(["read", "write"])]
    private $email;

    #[ORM\Column(type: 'json')]
    #[Groups(["read", "write"])]
    private $roles = [];

    #[ORM\Column(type: 'json')]
    #[Groups(["read", "write"])]
    private $zoneIntervention = [];

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $password;

    #[Assert\NotBlank(groups: ['POST'])]
    #[Groups(["read", "write"])]
    private $plainPassword;

    #[Assert\NotBlank()]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $firstName;

    #[Assert\NotBlank()]
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $lastName;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $enabled;

    #[ORM\Column(type: 'string', length: 10, unique: true)]
    #[Groups(["read", "write"])]
    private $phone;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $status;

    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(["read", "write"])]
    private $lastActivityAt;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isActiveNow;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private $adresse;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private $sexe;


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    // #[Groups(["read", "write"])]
    private $reset_token;

    #[ORM\OneToMany(mappedBy: 'sender', targetEntity: Message::class, orphanRemoval: true)]
    #[Groups(["read", "write"])]
    private Collection $sent;

    #[ORM\OneToMany(mappedBy: 'recipient', targetEntity: Message::class, orphanRemoval: true)]
    #[Groups(["read", "write"])]
    private Collection $received;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(["read", "write"])]
    private ?string $avatar = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private ?string $pass = null;


    #[ORM\ManyToOne(targetEntity: Departement::class)]
    #[Groups(["read", "write"])]
    private $departement;


    public function __construct()
    {
        $this->sent = new ArrayCollection();
        $this->received = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPass(): ?string
    {

        return $this->pass;
    }

    public function setPass(?string $pass): self
    {

        $this->pass = $pass;

        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getRoles(): array
    {
        $roles = $this->roles;
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(?bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }



    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * The public representation of the user (e.g. a username, an email address, etc.)
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        if ($this->email != null) {
            return (string) $this->email;
        } else {
            return (string) $this->phone;
        }
        return (string) $this->phone;
    }

    public function getUsername(): string
    {
        return (string) $this->getUserIdentifier();
    }
    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }


    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }



    public function getLastActivityAt(): ?\DateTimeInterface
    {
        return $this->lastActivityAt;
    }

    public function setLastActivityAt(?\DateTimeInterface $lastActivityAt): self
    {
        $this->lastActivityAt = $lastActivityAt;

        return $this;
    }

    public function getIsActiveNow(): ?bool
    {
        return $this->isActiveNow;
    }

    public function setIsActiveNow(?bool $isActiveNow): self
    {
        $this->isActiveNow = $isActiveNow;

        return $this;
    }
    public function isActiveNow()
    {
        // Delay during wich the user will be considered as still active
        $delay = new \DateTime('2 minutes ago');

        return ($this->getLastActivityAt() > $delay);
    }
    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getSexe(): ?string
    {
        return $this->sexe;
    }

    public function setSexe(?string $sexe): self
    {
        $this->sexe = $sexe;

        return $this;
    }
    public function getResetToken(): ?string
    {
        return $this->reset_token;
    }

    public function setResetToken(?string $reset_token): self
    {
        $this->reset_token = $reset_token;

        return $this;
    }


    /**
     * @return Collection<int, Message>
     */
    public function getSent(): Collection
    {
        return $this->sent;
    }

    public function addSent(Message $sent): self
    {
        if (!$this->sent->contains($sent)) {
            $this->sent[] = $sent;
            $sent->setSender($this);
        }

        return $this;
    }

    public function removeSent(Message $sent): self
    {
        if ($this->sent->removeElement($sent)) {
            // set the owning side to null (unless already changed)
            if ($sent->getSender() === $this) {
                $sent->setSender(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Message>
     */
    public function getReceived(): Collection
    {
        return $this->received;
    }

    public function addReceived(Message $received): self
    {
        if (!$this->received->contains($received)) {
            $this->received[] = $received;
            $received->setRecipient($this);
        }

        return $this;
    }

    public function removeReceived(Message $received): self
    {
        if ($this->received->removeElement($received)) {
            // set the owning side to null (unless already changed)
            if ($received->getRecipient() === $this) {
                $received->setRecipient(null);
            }
        }

        return $this;
    }


    public function getImage(): string
    {

        if (str_contains($this->avatar, "avatars")) {
            $data = file_get_contents($this->getAvatar());
            $img_code = "data:image/png;base64,{`base64_encode($data)`}";
            return $img_code;
        } else {
            return $this->avatar;
        }
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function isIsActiveNow(): ?bool
    {
        return $this->isActiveNow;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(string $avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function asArray(): array
    {

        $data = [
            'id' => $this->getId(),
            'username' => $this->getUsername(),
            'email' => $this->getEmail(),
            'roles' => $this->getRoles(),
            'zoneIntervention' => $this->getZoneIntervention(),
            'firstName' => $this->getFirstName(),
            'lastName' => $this->getLastName(),
            'phone' => $this->getPhone(),
            'enabled' => $this->getEnabled(),
            'isActiveNow' => $this->getIsActiveNow(),
            'lastActivityAt' => $this->getLastActivityAt(),
            'sexe' => $this->getSexe(),
            'status' => $this->getStatus(),
            'adresse' => $this->getAdresse(),
            'sent' => $this->getSent(),
            'received' => $this->getReceived(),
            'avatar' => $this->getAvatar(),
            'departement' => $this->departement->asArrayUser() ? $this->departement->asArrayUser() : null
        ];
        return $data;
    }

    public function getDepartement(): ?Departement
    {
        return $this->departement;
    }

    public function setDepartement(?Departement $departement): self
    {
        $this->departement = $departement;

        return $this;
    }

    public function getZoneIntervention(): array
    {
        $zoneIntervention = $this->zoneIntervention ? $this->zoneIntervention : [];
        // $roles[] = 'ROLE_USER';
        return array_unique($zoneIntervention);
    }

    public function setZoneIntervention(array $zoneIntervention): self
    {
        $this->zoneIntervention = $zoneIntervention;

        return $this;
    }
}
