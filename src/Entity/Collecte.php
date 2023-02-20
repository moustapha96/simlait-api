<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\CollecteRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: CollecteRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],

)]

class Collecte
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["read", "write"])]
    private $id;

    #[ORM\ManyToOne(targetEntity: Produits::class)]
    #[Groups(["read", "write"])]
    private $produits;

    #[ORM\ManyToOne(targetEntity: Conditionnements::class)]
    #[Groups(["read", "write"])]
    private $conditionnements;

    #[ORM\ManyToOne(targetEntity: Unites::class)]
    #[Groups(["read", "write"])]
    #[ORM\JoinColumn(name: "unites_id", referencedColumnName: "id")]
    private $unites;

    #[ORM\ManyToOne(targetEntity: UserMobile::class, inversedBy: 'collectes')]
    #[Groups(["read", "write"])]
    private $user;


    #[ORM\ManyToOne(targetEntity: Emballage::class)]
    #[Groups(["read", "write"])]
    private $emballages;


    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(["read", "write"])]
    private $dateCollecte;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isSynchrone;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isCertified;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(["read", "write"])]
    private $quantite;

    #[ORM\Column(type: 'float', nullable: true)]
    #[Groups(["read", "write"])]
    private $prix;

    #[ORM\Column(type: 'boolean', nullable: true)]
    #[Groups(["read", "write"])]
    private $isDeleted;



    #[ORM\Column(type: 'datetime', nullable: true)]
    #[Groups(["read", "write"])]
    private $dateSaisie;

    #[ORM\Column(nullable: true)]
    #[Groups(["read", "write"])]
    private ?int $quantite_vendu = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["read", "write"])]
    private ?int $quantite_autre = null;

    #[ORM\Column(nullable: true)]
    #[Groups(["read", "write"])]
    private ?int $quantite_perdu = null;


    public function getId(): ?int
    {
        return $this->id;
    }
    public function getUser(): ?UserMobile
    {
        return $this->user;
    }

    public function setUser(?UserMobile $user): self
    {
        $this->user = $user;
        return $this;
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


    public function getEmballages(): ?Emballage
    {
        return $this->emballages;
    }

    public function setEmballages(?Emballage $emballages): self
    {
        $this->emballages = $emballages;

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

    public function getQuantite(): ?float
    {
        return $this->quantite;
    }

    public function setQuantite(?float $quantite): self
    {
        $this->quantite = $quantite;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(?float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

    public function getIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }

    public function setIsDeleted(?bool $isDeleted): self
    {
        $this->isDeleted = $isDeleted;
        // if ($this->isDeleted == true) {
        //     $this->isSynchrone = false;
        //     $this->isCertified = false;
        // }

        return $this;
    }


    public function asArray(): array
    {
        return [
            'id' => $this->getId(),
            'produits' => $this->produits->asArray(),
            'conditionnements' => $this->conditionnements->asArray(),
            'unites' => $this->unites->asArray(),
            'user' => $this->user->asArray(),
            'emballages' => $this->emballages->asArray(),
            'dateCollecte' => $this->dateCollecte,
            'isSynchrone' => $this->isSynchrone,
            'isCertified' => $this->isCertified,
            'quantite' => $this->quantite,
            'prix' => $this->prix,
            'isDeleted' => $this->isDeleted,
            'quantite_vendu' => $this->quantite_vendu,
            'quantite_autre' => $this->quantite_autre,
            'quantite_perdu' => $this->quantite_perdu,
            'dateSaisie' => $this->dateSaisie
        ];
    }
    public function asArray2(): array
    {
        return [
            'id' => $this->getId(),
            'produits' => $this->produits->asArray(),
            'conditionnements' => $this->conditionnements->asArray(),
            'unites' => $this->unites->asArray(),
            'user' => $this->user,
            'emballages' => $this->emballages->asArray(),
            'dateCollecte' => $this->dateCollecte,
            'isSynchrone' => $this->isSynchrone,
            'isCertified' => $this->isCertified,
            'quantite' => $this->quantite,
            'prix' => $this->prix,
            'isDeleted' => $this->isDeleted,
            'quantite_vendu' => $this->quantite_vendu,
            'quantite_autre' => $this->quantite_autre,
            'quantite_perdu' => $this->quantite_perdu,
            'dateSaisie' => $this->dateSaisie
        ];
    }

    public function isIsSynchrone(): ?bool
    {
        return $this->isSynchrone;
    }

    public function isIsCertified(): ?bool
    {
        return $this->isCertified;
    }

    public function isIsDeleted(): ?bool
    {
        return $this->isDeleted;
    }


    public function getQuantiteVendu(): ?int
    {
        return $this->quantite_vendu;
    }

    public function setQuantiteVendu(?int $quantite_vendu): self
    {
        $this->quantite_vendu = $quantite_vendu;
        return $this;
    }

    public function getQuantiteAutre(): ?int
    {
        return $this->quantite_autre;
    }

    public function setQuantiteAutre(?int $quantite_autre): self
    {
        $this->quantite_autre = $quantite_autre;
        return $this;
    }

    public function getQuantitePerdu(): ?int
    {
        return $this->quantite_perdu;
    }

    public function setQuantitePerdu(?int $quantite_perdu): self
    {
        $this->quantite_perdu = $quantite_perdu;
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

    public function getDateSaisie(): ?string
    {
        return $this->dateSaisie->format('d/m/Y');;
    }

    public function setDateSaisie(?\DateTimeInterface $dateSaisie): self
    {
        $this->dateSaisie = $dateSaisie;

        return $this;
    }
    public function getDateCollecte(): ?string
    {
        return $this->dateCollecte->format('d/m/Y');
    }

    public function setDateCollecte(?\DateTimeInterface $dateCollecte): self
    {
        if (is_string($dateCollecte)) {

            $date = new DateTime($dateCollecte);
            $this->dateCollecte = $date;
        } else {
            $this->dateCollecte = $dateCollecte;
        }
        return $this;
    }
}
