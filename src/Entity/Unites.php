<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\UnitesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UnitesRepository::class)]
#[ApiResource(
    denormalizationContext: ['groups' => ['write']],
    normalizationContext: ['groups' => ['read']],
)
]
class Unites
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(["read", "write"])]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $nom;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private $telephone;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private $email;

    #[ORM\Column(type: 'datetime_immutable')]
    #[Groups(["read", "write"])]
    private $createdAt;

    #[ORM\Column(type: 'string',length: 255)]
    #[Groups(["read", "write"])]
    private $latitude;

    #[ORM\Column(type: 'string',length: 255)]
    #[Groups(["read", "write"])]
    private $longitude;

    #[ORM\ManyToOne(targetEntity: Region::class)]
    #[Groups([ "write"])]
    private $region;

    #[ORM\ManyToOne(targetEntity: Departement::class)]
    #[Groups(["read", "write"])]
    private $departement;

    #[ORM\ManyToOne(targetEntity: Zones::class)]
    #[Groups(["read", "write"])]
    private $zone;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $adresse;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isSynchrone;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isCertified;


    #[ORM\ManyToOne(targetEntity: UserMobile::class, inversedBy: 'unites')]
    #[Groups(["read", "write"])]
    private $userMobile;

    #[ORM\Column(length: 255)]
    #[Groups(["read", "write"])]
    private ?string $prenomProprietaire = null;

    #[ORM\Column(length: 255)]
    #[Groups(["read", "write"])]
    private ?string $nomProprietaire = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private ?string $localite = null;

    #[ORM\ManyToOne(inversedBy: 'unites')]
    #[Groups(["read", "write"])]
    private ?Profils $profil = null;

    #[ORM\OneToMany(mappedBy: 'unites', targetEntity: UnitesDemande::class)]
    private Collection $unitesDemandes;


    public function __construct()
    {
        // $this->produits = new ArrayCollection();
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

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): self
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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(string $lat): self
    {
        $this->latitude = $lat;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(string $long): self
    {
        $this->longitude = $long;

        return $this;
    }

    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
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


    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

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


    public function getZone(): ?Zones
    {
        return $this->zone;
    }

    public function setZone(?Zones $zone): self
    {
        $this->zone = $zone;

        return $this;
    }



    public function asArray(): array
    {
        return [
            'id'=> $this->getId(),
            'nom' => $this->nom,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'prenomProprietaire' => $this->prenomProprietaire,
            'nomProprietaire' => $this->nomProprietaire,
            'createdAt' => $this->createdAt->format('d/m/Y'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'departement' => $this->departement->asArray(),
            'adresse' => $this->adresse,
            'isSynchrone' =>$this->isSynchrone,
            'isCertified'=>$this->isCertified ,
            'zone' => $this->zone->asArray(),
            'region' => $this->region->asArray(),
            'userMobile'=> $this->userMobile->asArray(),
            'localite'=> $this->localite,
            'profil' => $this->profil->asArray()

        ];

    }

    public function asArraygetDepartement(){

        return [
            'id'=> $this->getId(),
            'nom' => $this->nom,
            'telephone' => $this->telephone,
            'email' => $this->email,
            'prenomProprietaire' => $this->prenomProprietaire,
            'nomProprietaire' => $this->nomProprietaire,
            'createdAt' => $this->createdAt->format('d/m/Y'),
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'departement' => $this->departement->asArray(),
            'adresse' => $this->adresse,
            'isSynchrone' =>$this->isSynchrone,
            'isCertified'=>$this->isCertified ,
            'zone' => $this->zone->asArraygetDepartement(),
            'region' => $this->region->asArray(),
            'userMobile'=> $this->userMobile->asArray(),
            'localite'=> $this->localite,
            'profil' => $this->profil->asArray()
        ];
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

    public function getPrenomProprietaire(): ?string
    {
        return $this->prenomProprietaire;
    }

    public function setPrenomProprietaire(string $prenomProprietaire): self
    {
        $this->prenomProprietaire = $prenomProprietaire;

        return $this;
    }

    public function getNomProprietaire(): ?string
    {
        return $this->nomProprietaire;
    }

    public function setNomProprietaire(string $nomProprietaire): self
    {
        $this->nomProprietaire = $nomProprietaire;

        return $this;
    }

    public function isIsSynchrone(): ?bool
    {
        return $this->isSynchrone;
    }

    public function isIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function getLocalite(): ?string
    {
        return $this->localite;
    }

    public function setLocalite(?string $localite): self
    {
        $this->localite = $localite;

        return $this;
    }

    public function getProfil(): ?Profils
    {
        return $this->profil;
    }

    public function setProfil(?Profils $profil): self
    {
        $this->profil = $profil;

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
            $unitesDemande->setUnites($this);
        }

        return $this;
    }

    public function removeUnitesDemande(UnitesDemande $unitesDemande): self
    {
        if ($this->unitesDemandes->removeElement($unitesDemande)) {
            // set the owning side to null (unless already changed)
            if ($unitesDemande->getUnites() === $this) {
                $unitesDemande->setUnites(null);
            }
        }

        return $this;
    }


}
