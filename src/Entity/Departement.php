<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DepartementRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;


#[ORM\Entity(repositoryClass: DepartementRepository::class)]
#[ApiResource(
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']],

)]

#[ORM\Table(name: '`simlait_departements`')]
class Departement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    #[Groups(["read", "write"])]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["read", "write"])]
    private $nom;

    #[ORM\ManyToOne(targetEntity: Region::class, inversedBy: 'departement')]
    #[Groups(["read", "write"])]
    private $region;

    #[ORM\ManyToOne(targetEntity: Zones::class, inversedBy: 'departements')]
    #[Groups(["read", "write"])]
    protected $zones;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private ?string $longitude = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(["read", "write"])]
    private ?string $latitude = null;


    public $data_localisation;

    public function __construct()
    {
    }

    public function __toString()
    {
        return sprintf('Departement #%d', $this->getId());
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



    public function getRegion(): ?Region
    {
        return $this->region;
    }

    public function setRegion(?Region $region): self
    {
        $this->region = $region;

        return $this;
    }

    public function getZones(): ?Zones
    {
        return $this->zones;
    }

    public function setZones(?Zones $zones): self
    {
        $this->zones = $zones;

        return $this;
    }

    public function asArray(): ?array
    {
        return [
            "id" => $this->id,
            "nom" => $this->nom,
            "region" => $this->region->asArray(),
            "zones" =>  $this->zones ? $this->zones->asArraygetWithOutDepartement() : null,
            "latitude" => $this->getLatitude(),
            "longitude" => $this->getLongitude()
        ];
    }

    public function asArrayUser(): ?array
    {
        return [
            "id" => $this->id,
            "nom" => $this->nom,
            "region" => [
                "id" => $this->region->getId(),
                "nom" => $this->region->getNom()
            ],
            "zone" =>  $this->getZones() != null ? [
                "id" => $this->getZones()->getId(),
                "nom" => $this->getZones()->getNom()
            ] : null,
            "latitude" => $this->getLatitude(),
            "longitude" => $this->getLongitude()
        ];
    }

    public function asArraySimple(): ?array
    {
        return [
            "id" => $this->id,
            "nom" => $this->nom,
            "region" => [
                "id" => $this->region->getId(),
                "nom" => $this->region->getNom()
            ],
            "zone" => $this->getZones() != null ? [
                "id" => $this->getZones()->getId(),
                "nom" => $this->getZones()->getNom(),
                "description" => $this->getZones()->getDescription(),
                "statut" => $this->getZones()->getStatut(),
            ] : null,
            "latitude" => $this->getLatitude(),
            "longitude" => $this->getLongitude()
        ];
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {

        $file = 'config/coordinates.json';
        $jsonData = file_get_contents($file);
        $data_localisation = json_decode($jsonData, true);

        $this->longitude = $longitude;
        if (empty($longitude)) {
            $nom = $this->getNom();
            if (isset($data_localisation[$nom]['longitude'])) {
                $this->longitude = $data_localisation[$nom]['longitude'];
            }
        }


        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {

        $file = 'config/coordinates.json';
        $jsonData = file_get_contents($file);
        $data_localisation = json_decode($jsonData, true);

        $this->latitude = $latitude;
        if (empty($latitude)) {
            $nom = $this->getNom();
            if (isset($data_localisation[$nom]['latitude'])) {
                $this->latitude = $data_localisation[$nom]['latitude'];
            }
        }

        return $this;
    }
}
