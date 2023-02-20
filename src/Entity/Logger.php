<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\LoggerRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoggerRepository::class)]
#[ApiResource]
class Logger
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'string', length: 255)]
    private $host;

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(type: 'datetime' , nullable: true)]
    private $dateRequest;

    #[ORM\Column(type: 'string', length: 255)]
    private $statutCode;


    #[ORM\Column(type: 'text', nullable: true)]
    private $responseContent = '';


    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $method;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $requestUri;

    #[ORM\Column(type: 'array')]
    private $requestContent = [];

    #[ORM\Column(type: 'string', length: 255)]
    private $fromApp;
    
   
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function setHost(string $host): self
    {
        $this->host = $host;

        return $this;
    }

  
    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
       
        $this->email = $email;


        return $this;
    }

    public function getDateRequest(): ?\DateTime
    {
        return $this->dateRequest;
    }

    public function setDateRequest(\DateTime $dateRequest): self
    {
        $this->dateRequest = $dateRequest;

        return $this;
    }

    public function getStatutCode(): ?string
    {
        return $this->statutCode;
    }

    public function setStatutCode(string $statutCode): self
    {
        $this->statutCode = $statutCode;

        return $this;
    }


    public function getResponseContent(): ?string
    {
        return $this->responseContent;
    }

    public function setResponseContent(?string $responseContent): self
    {
        $this->responseContent = $responseContent;

        return $this;
    }


    public function getMethod(): ?string
    {
        return $this->method;
    }

    public function setMethod(?string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getRequestUri(): ?string
    {
        return $this->requestUri;
    }

    public function setRequestUri(?string $requestUri): self
    {
        $this->requestUri = $requestUri;

        return $this;
    }

   
    public function getRequestContent(): ?array
    {
        return $this->requestContent;
    }

    public function setRequestContent(array $content): self
    {
        $this->requestContent = $content;
        return $this;
    }

    public function getFromApp(): ?string
    {
        return $this->fromApp;
    }

    public function setFromApp(string $fromApp): self
    {
        $this->fromApp = $fromApp;

        return $this;
    }


  
        public function asArray(): string
    {
        $data =  [
            // 'id' => $this->id,
            'host' => $this->host,
            'email' => $this->email,
            'dateRequest' => $this->dateRequest,
            // 'responseContent' => $this->responseContent,
            'method' => $this->method,
            'requestUri' => $this->requestUri,
            // 'requestContent' => $this->requestContent,
            'fromApp' => $this->fromApp,
        ];
        return  json_encode($data); 
    }
  
}
