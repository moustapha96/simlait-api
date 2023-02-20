<?php

namespace App\Entity;

use App\Repository\UnitesAutreRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UnitesAutreRepository::class)]
class UnitesAutre
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $nom;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $prenom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private $telephone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private $email;

   

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $adresse;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["read", "write"])]
    private $createdAt;

    #[ORM\OneToMany(mappedBy: 'unitesAutre', targetEntity: UnitesDemande::class)]
    private Collection $unitesDemandes;

    public function __construct()
    {
        $this->unitesDemandes = new ArrayCollection();
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(?string $telephone): self
    {
        $this->telephone = $telephone;

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

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

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

    /**
     * @return Collection<int, UnitesDemande>
     */
    public function getUnitesDemandes(): Collection
    {
        return $this->unitesDemandes;
    }

    public function addUnitesDemande(UnitesDemande $unitesDemande): self
    {
        if (!$this->unitesDemandes->contains($unitesDemande)) {
            $this->unitesDemandes->add($unitesDemande);
            $unitesDemande->setUnitesAutre($this);
        }

        return $this;
    }

    public function removeUnitesDemande(UnitesDemande $unitesDemande): self
    {
        if ($this->unitesDemandes->removeElement($unitesDemande)) {
            // set the owning side to null (unless already changed)
            if ($unitesDemande->getUnitesAutre() === $this) {
                $unitesDemande->setUnitesAutre(null);
            }
        }

        return $this;
    }

   
}
